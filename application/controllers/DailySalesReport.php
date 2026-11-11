<?php
/*
  ###########################################################
  # This is DailySalesReport Controller
  # Emails the day's sales summary to the owner. Command line only:
  #   php index.php DailySalesReport send [YYYY-MM-DD]
  #   php index.php DailySalesReport preview [YYYY-MM-DD]   (prints the email, sends nothing)
  # Recipients come from $config['daily_sales_report_to'] in config.php (comma separated).
  ###########################################################
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class DailySalesReport extends CI_Controller {

    private $company_id = 1;

    public function __construct() {
        parent::__construct();
        if (!is_cli()) {
            show_404();
        }
        $zone = $this->db->select('zone_name')->where('id', $this->company_id)->get('tbl_companies')->row();
        if ($zone && $zone->zone_name) {
            date_default_timezone_set($zone->zone_name);
        }
    }

    /**
     * send the report for a day (today by default)
     * @param string
     */
    public function send($date = '') {
        $date = $this->validDate($date);
        $to = trim((string)$this->config->item('daily_sales_report_to'));
        if ($to == '') {
            echo "No recipients: set \$config['daily_sales_report_to'] in config.php\n";
            exit(1);
        }
        $report = $this->buildReport($date);
        $failed = array();
        foreach (array_filter(array_map('trim', explode(',', $to))) as $email) {
            if (!sendEmailOnlyAZ($report['subject'], $report['html'], $email, '', '', $this->company_id)) {
                $failed[] = $email;
            }
        }
        if ($failed) {
            log_message('error', 'Daily sales report for '.$date.' could not be sent to '.implode(', ', $failed));
            echo "FAILED for ".implode(', ', $failed)."\n";
            exit(1);
        }
        echo "Sent: ".$report['subject']." -> ".$to."\n";
    }

    /**
     * print the report without sending it
     * @param string
     */
    public function preview($date = '') {
        $report = $this->buildReport($this->validDate($date));
        echo "Subject: ".$report['subject']."\n\n".$report['html']."\n";
    }

    private function validDate($date) {
        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        return date('Y-m-d');
    }

    private function outletIds() {
        $rows = $this->db->select('id')->where('company_id', $this->company_id)->where('del_status', 'Live')->get('tbl_outlets')->result();
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int)$row->id;
        }
        return $ids ? $ids : array(0);
    }

    /**
     * closed sales of a day, the same rule as the Daily Summary report
     */
    private function salesQuery($date, $outlets) {
        return $this->db->from('tbl_sales')
            ->where('sale_date', $date)
            ->where_in('outlet_id', $outlets)
            ->where('order_status', 3)
            ->where('del_status', 'Live');
    }

    private function totals($date, $outlets) {
        $row = $this->salesQuery($date, $outlets)
            ->select('COUNT(*) AS orders, COALESCE(SUM(sub_total),0) AS items, COALESCE(SUM(total_discount_amount),0) AS discount, COALESCE(SUM(delivery_charge_actual_charge),0) AS delivery, COALESCE(SUM(vat),0) AS tax, COALESCE(SUM(total_payable),0) AS net', FALSE)
            ->get()->row();
        return $row;
    }

    private function buildReport($date) {
        $outlets = $this->outletIds();
        $outlet_list = implode(',', $outlets);
        $company = $this->db->where('id', $this->company_id)->get('tbl_companies')->row();
        $currency = $company && $company->currency ? $company->currency : '';
        $money = function ($amount) use ($currency) {
            return $currency.number_format((float)$amount, 2);
        };

        $t = $this->totals($date, $outlets);
        $last_week = $this->totals(date('Y-m-d', strtotime($date.' -7 days')), $outlets);
        $orders = (int)$t->orders;

        $payments = $this->db->query("SELECT COALESCE(m.name, p.payment_name, 'Other') AS name, COUNT(DISTINCT p.sale_id) AS orders, SUM(p.amount) AS amount
            FROM tbl_sale_payments p JOIN tbl_sales s ON s.id = p.sale_id LEFT JOIN tbl_payment_methods m ON m.id = p.payment_id
            WHERE s.sale_date = ? AND s.outlet_id IN ($outlet_list) AND s.order_status = 3 AND s.del_status = 'Live' AND p.del_status = 'Live'
            GROUP BY 1 ORDER BY amount DESC", array($date))->result();

        $order_types = $this->db->query("SELECT order_type, COUNT(*) AS orders, SUM(total_payable) AS amount FROM tbl_sales
            WHERE sale_date = ? AND outlet_id IN ($outlet_list) AND order_status = 3 AND del_status = 'Live'
            GROUP BY order_type ORDER BY amount DESC", array($date))->result();

        $top_items = $this->db->query("SELECT d.menu_name AS name, SUM(d.qty) AS qty, SUM(d.menu_price_with_discount) AS amount
            FROM tbl_sales_details d JOIN tbl_sales s ON s.id = d.sales_id
            WHERE s.sale_date = ? AND s.outlet_id IN ($outlet_list) AND s.order_status = 3 AND s.del_status = 'Live' AND d.del_status = 'Live'
            GROUP BY d.menu_name ORDER BY amount DESC LIMIT 10", array($date))->result();

        $cashiers = $this->db->query("SELECT COALESCE(u.full_name, 'Unknown') AS name, COUNT(*) AS orders, SUM(s.total_payable) AS amount
            FROM tbl_sales s LEFT JOIN tbl_users u ON u.id = s.user_id
            WHERE s.sale_date = ? AND s.outlet_id IN ($outlet_list) AND s.order_status = 3 AND s.del_status = 'Live'
            GROUP BY 1 ORDER BY amount DESC", array($date))->result();

        $open = $this->db->query("SELECT COUNT(*) AS orders, COALESCE(SUM(total_payable),0) AS amount FROM tbl_sales
            WHERE sale_date = ? AND outlet_id IN ($outlet_list) AND order_status IN (1,2) AND del_status = 'Live'", array($date))->row();

        $refunds = $this->db->query("SELECT COUNT(*) AS orders, COALESCE(SUM(total_refund),0) AS amount FROM tbl_sales
            WHERE DATE(refund_date_time) = ? AND outlet_id IN ($outlet_list) AND del_status = 'Live' AND total_refund > 0", array($date))->row();

        $expenses = $this->db->query("SELECT COUNT(*) AS entries, COALESCE(SUM(amount),0) AS amount FROM tbl_expenses
            WHERE date = ? AND outlet_id IN ($outlet_list) AND del_status = 'Live'", array($date))->row();

        $cancelled = $this->db->query("SELECT COUNT(*) AS n FROM tbl_audit_logs WHERE date = ? AND event_title = 'Cancelled Sale' AND outlet_id IN ($outlet_list)", array($date))->row();
        $removed = $this->db->query("SELECT COUNT(*) AS n FROM tbl_audit_logs WHERE date = ? AND event_title = 'Remove Item' AND outlet_id IN ($outlet_list)", array($date))->row();

        $type_names = array(1 => 'Dine In', 2 => 'Take Away', 3 => 'Delivery');
        $day_label = date('D, j M Y', strtotime($date));
        $name = $company ? html_entity_decode($company->business_name, ENT_QUOTES, 'UTF-8') : 'Restaurant';

        if ((float)$last_week->net > 0) {
            $change = ((float)$t->net - (float)$last_week->net) / (float)$last_week->net * 100;
            $compare = ($change >= 0 ? '▲ ' : '▼ ').number_format(abs($change), 0).'% vs '.date('D, j M', strtotime($date.' -7 days')).' ('.$money($last_week->net).', '.(int)$last_week->orders.' orders)';
        } else {
            $compare = 'No sales on '.date('D, j M', strtotime($date.' -7 days')).' to compare';
        }

        $e = function ($text) {
            return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
        };
        $cell = 'padding:6px 8px;border-bottom:1px solid #eee;';
        $num = $cell.'text-align:right;white-space:nowrap;';
        $table = function ($title, $head, $rows) use ($e, $cell, $num) {
            if (!$rows) {
                return '';
            }
            $html = '<h3 style="margin:24px 0 8px;font-size:16px;color:#2d1305;">'.$e($title).'</h3><table style="width:100%;border-collapse:collapse;font-size:14px;"><tr>';
            foreach ($head as $i => $h) {
                $html .= '<th style="'.($i ? $num : $cell).'color:#875428;font-weight:600;">'.$e($h).'</th>';
            }
            $html .= '</tr>';
            foreach ($rows as $r) {
                $html .= '<tr>';
                foreach ($r as $i => $v) {
                    $html .= '<td style="'.($i ? $num : $cell).'">'.$e($v).'</td>';
                }
                $html .= '</tr>';
            }
            return $html.'</table>';
        };

        $summary = array(
            array('Orders (closed)', $orders),
            array('Items sold', $money($t->items)),
            array('Discounts', '-'.$money($t->discount)),
        );
        if ((float)$t->delivery > 0) {
            $summary[] = array('Delivery fees', $money($t->delivery));
        }
        if ((float)$t->tax > 0) {
            $summary[] = array('Tax', $money($t->tax));
        }
        $summary[] = array('Average per order', $money($orders ? $t->net / $orders : 0));

        $payment_rows = array();
        foreach ($payments as $p) {
            $payment_rows[] = array($p->name, (int)$p->orders, $money($p->amount));
        }
        $type_rows = array();
        foreach ($order_types as $o) {
            $type_rows[] = array(isset($type_names[$o->order_type]) ? $type_names[$o->order_type] : 'Other', (int)$o->orders, $money($o->amount));
        }
        $item_rows = array();
        foreach ($top_items as $i) {
            $item_rows[] = array(html_entity_decode($i->name, ENT_QUOTES, 'UTF-8'), (float)$i->qty + 0, $money($i->amount));
        }
        $cashier_rows = array();
        foreach ($cashiers as $c) {
            $cashier_rows[] = array($c->name, (int)$c->orders, $money($c->amount));
        }
        $other_rows = array(
            array('Refunds', (int)$refunds->orders, $money($refunds->amount)),
            array('Expenses recorded', (int)$expenses->entries, $money($expenses->amount)),
            array('Cancelled orders', (int)$cancelled->n, ''),
            array('Items removed from orders', (int)$removed->n, ''),
        );

        $warning = '';
        if ((int)$open->orders > 0) {
            $warning = '<p style="margin:16px 0 0;padding:10px 12px;background:#fff4e5;border-left:4px solid #e08a00;font-size:14px;">'
                .(int)$open->orders.' order'.((int)$open->orders == 1 ? ' is' : 's are').' still open (not paid yet, '.$e($money($open->amount)).') and not included above.</p>';
        }

        $html = '<div style="font-family:Arial,Helvetica,sans-serif;color:#222;max-width:640px;margin:0 auto;">'
            .'<h2 style="margin:0 0 4px;color:#2d1305;">'.$e($name).' daily sales</h2>'
            .'<p style="margin:0 0 16px;color:#666;">'.$e($day_label).' · sent '.$e(date('g:i a')).'</p>'
            .'<div style="background:#f5ebe2;padding:16px;border-radius:8px;">'
            .'<div style="font-size:13px;color:#875428;">Net sales</div>'
            .'<div style="font-size:30px;font-weight:bold;color:#2d1305;">'.$e($money($t->net)).'</div>'
            .'<div style="font-size:13px;color:#555;margin-top:4px;">'.$e($compare).'</div></div>'
            .$warning
            .$table('Summary', array('', ''), $summary)
            .$table('By payment method', array('Method', 'Orders', 'Amount'), $payment_rows)
            .$table('By order type', array('Type', 'Orders', 'Amount'), $type_rows)
            .$table('Top 10 items', array('Item', 'Qty', 'Amount'), $item_rows)
            .$table('By cashier', array('Staff', 'Orders', 'Amount'), $cashier_rows)
            .$table('Other activity', array('', 'Count', 'Amount'), $other_rows)
            .'<p style="margin:24px 0 0;font-size:12px;color:#888;">Closed orders only, same as the Daily Summary report. Full details: '
            .'<a href="'.$e(base_url().'Report/dailySummaryReport').'" style="color:#875428;">Daily Summary report</a>.</p>'
            .'</div>';

        return array(
            'subject' => $name.' sales '.date('D j M', strtotime($date)).': '.$money($t->net).' ('.$orders.' order'.($orders == 1 ? '' : 's').')',
            'html' => $html,
        );
    }
}
