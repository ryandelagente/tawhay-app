<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ZATCA Invoice Library - Optimized Version
 * 
 * Handles ZATCA Phase-2 E-Invoice generation, signing, and submission
 * Optimized for performance - accepts data directly without DB queries
 * 
 * @package    iRestora PLUS
 * @subpackage Libraries
 * @category   ZATCA Integration
 * @author     Door Soft
 * @version    2.0.0 (Performance Optimized)
 */
class Zatca_invoice {
    
    protected $CI;
    protected $zatca_outlet;
    protected $zatca_token;
    protected $environment;
    
    /**
     * Constructor
     */
    public function __construct() {
        "use strict";
        $this->CI =& get_instance();
        $this->CI->config->load('zatca_config', TRUE);
    }
    
    /**
     * Submit Credit Note to ZATCA (for refunds/returns)
     * 
     * @param int $sale_id Original Sale ID
     * @param object $refund_data Refund data with items (pre-loaded)
     * @param object $original_invoice_data Original invoice ZATCA data
     * @param object $outlet_data Outlet ZATCA configuration (pre-loaded)
     * @return array Result with success status and message
     */
    public function submitCreditNote($sale_id, $refund_data, $original_invoice_data, $outlet_data) {
        "use strict";
        $result = array(
            'success' => false,
            'message' => '',
            'data' => array()
        );
        
        try {
            // Validate inputs
            if (!$refund_data || !$outlet_data || !$original_invoice_data) {
                $result['message'] = 'Invalid input data for credit note';
                return $result;
            }
            
            // Validate original invoice has ZATCA data
            if (!isset($original_invoice_data->zatca_response) || !$original_invoice_data->zatca_response) {
                $result['message'] = 'Original invoice does not have ZATCA data';
                $this->saveErrorToDatabase($sale_id, 'Credit Note: Original invoice missing ZATCA data', 'Original invoice must be submitted to ZATCA before creating credit note');
                return $result;
            }
            
            // Load and validate ZATCA configuration
            if (!$this->loadZatcaConfig($outlet_data)) {
                $result['message'] = 'ZATCA configuration invalid';
                $this->saveErrorToDatabase($sale_id, 'Credit Note: No outlet ZATCA configuration found', 'Outlet not connected to ZATCA or zatca_outlet/zatca_token is NULL');
                return $result;
            }
            
            // Parse original invoice ZATCA data
            $original_zatca = json_decode($original_invoice_data->zatca_response);
            if (!isset($original_zatca->uuid) || !isset($original_zatca->invoice_hash)) {
                $result['message'] = 'Original invoice ZATCA data incomplete';
                $this->saveErrorToDatabase($sale_id, 'Credit Note: Original invoice ZATCA data missing UUID or hash', 'Original invoice ZATCA response is incomplete');
                return $result;
            }
            
            // Generate UUID for credit note
            $credit_note_uuid = $this->generateUUID();
            
            // Generate credit note XML
            $credit_note_xml = $this->generateCreditNoteXML($refund_data, $credit_note_uuid, $original_zatca);
            if (!$credit_note_xml) {
                $result['message'] = 'Credit Note XML generation failed';
                $this->saveErrorToDatabase($sale_id, 'Failed to generate credit note XML', 'Check refund data completeness and XML generation logic');
                return $result;
            }
            
            // Sign credit note
            $signed_data = $this->signInvoice($credit_note_xml);
            if (!$signed_data) {
                $result['message'] = 'Credit Note signing failed';
                $this->saveErrorToDatabase($sale_id, 'Failed to sign credit note', 'Check private key and certificate in zatca_outlet');
                return $result;
            }
            
            // Generate QR code for credit note
            $qr_code = $this->generateCreditNoteQRCode($refund_data, $credit_note_uuid);
            
            // Submit to ZATCA Clearance API (Credit Notes use Clearance API)
            $api_response = $this->callZatcaClearanceApi(
                $signed_data['hash'],
                $credit_note_uuid,
                $signed_data['signed_xml']
            );
            
            // Update database with credit note data
            $this->updateSaleZatcaCreditNoteData(
                $sale_id,
                $credit_note_uuid,
                $signed_data['hash'],
                $signed_data['signed_xml'],
                $qr_code,
                $api_response,
                $original_zatca->uuid
            );
            
            $result['success'] = $api_response['success'];
            $result['message'] = $api_response['message'];
            $result['data'] = $api_response;
            
        } catch (Exception $e) {
            $result['message'] = 'Exception: ' . $e->getMessage();
            $this->logError('ZATCA Credit Note Exception: ' . $e->getMessage());
            
            // Save exception to database
            $this->saveErrorToDatabase($sale_id, 'Credit Note Exception: ' . $e->getMessage(), $e->getTraceAsString());
        }
        
        return $result;
    }
    
    /**
     * Submit invoice to ZATCA (Optimized - accepts data directly)
     * 
     * @param int $sale_id Sale ID
     * @param object $sale_data Sale data with items (pre-loaded)
     * @param object $outlet_data Outlet ZATCA configuration (pre-loaded)
     * @return array Result with success status and message
     */
    public function submitInvoice($sale_id, $sale_data, $outlet_data) {
        "use strict";
        $result = array(
            'success' => false,
            'message' => '',
            'data' => array()
        );
        
        try {
            // Validate inputs
            if (!$sale_data || !$outlet_data) {
                $result['message'] = 'Invalid input data';
                return $result;
            }
            
            // Load and validate ZATCA configuration
            if (!$this->loadZatcaConfig($outlet_data)) {
                $result['message'] = 'ZATCA configuration invalid';
                $this->saveErrorToDatabase($sale_id, 'No outlet ZATCA configuration found', 'Outlet not connected to ZATCA or zatca_outlet/zatca_token is NULL');
                return $result;
            }
            
            // Generate UUID
            $invoice_uuid = $this->generateUUID();
            
            // Generate invoice XML
            $invoice_xml = $this->generateInvoiceXML($sale_data, $invoice_uuid);
            if (!$invoice_xml) {
                $result['message'] = 'XML generation failed';
                $this->saveErrorToDatabase($sale_id, 'Failed to generate invoice XML', 'Check sale data completeness and XML generation logic');
                return $result;
            }
            
            // Sign invoice
            $signed_data = $this->signInvoice($invoice_xml);
            if (!$signed_data) {
                $result['message'] = 'Invoice signing failed';
                $this->saveErrorToDatabase($sale_id, 'Failed to sign invoice', 'Check private key and certificate in zatca_outlet');
                return $result;
            }
            
            // Generate QR code
            $qr_code = $this->generateQRCode($sale_data, $invoice_uuid);
            
            // Submit to ZATCA API
            $api_response = $this->callZatcaApi(
                $signed_data['hash'],
                $invoice_uuid,
                $signed_data['signed_xml']
            );
            
            // Update database
            $this->updateSaleZatcaData(
                $sale_id,
                $invoice_uuid,
                $signed_data['hash'],
                $signed_data['signed_xml'],
                $qr_code,
                $api_response
            );
            
            $result['success'] = $api_response['success'];
            $result['message'] = $api_response['message'];
            $result['data'] = $api_response;
            
        } catch (Exception $e) {
            $result['message'] = 'Exception: ' . $e->getMessage();
            $this->logError('ZATCA Exception: ' . $e->getMessage());
            
            // Save exception to database
            $this->saveErrorToDatabase($sale_id, 'Exception: ' . $e->getMessage(), $e->getTraceAsString());
        }
        
        return $result;
    }
    
    /**
     * Load ZATCA configuration
     * 
     * @param object $outlet_data Outlet data
     * @return bool Success status
     */
    private function loadZatcaConfig($outlet_data) {
        "use strict";
        if (!$outlet_data->zatca_outlet || !$outlet_data->zatca_token) {
            return false;
        }
        
        $this->zatca_outlet = json_decode($outlet_data->zatca_outlet);
        $this->zatca_token = json_decode($outlet_data->zatca_token);
        
        // Get environment from token, or fallback to config setting
        if (isset($this->zatca_token->environment)) {
            $this->environment = $this->zatca_token->environment;
        } else {
            $config = $this->CI->config->item('zatca_config');
            $this->environment = $config['zatca_is_production'] ? 'production' : 'sandbox';
        }
        
        return ($this->zatca_outlet && $this->zatca_token);
    }
    
    /**
     * Generate UUID v4
     * 
     * @return string UUID
     */
    private function generateUUID() {
        "use strict";
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Generate invoice XML (Optimized)
     * 
     * @param object $sale_data Sale data
     * @param string $uuid Invoice UUID
     * @return string|null XML content
     */
    private function generateInvoiceXML($sale_data, $uuid) {
        "use strict";
        try {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = false;
            
            // Get config
            $config = $this->CI->config->item('zatca_config');
            $invoice_type = $config['zatca_default_invoice_type'];
            $currency = $config['zatca_currency_code'];
            
            // Root element
            $invoice = $xml->createElement('Invoice');
            $invoice->setAttribute('xmlns', $config['zatca_xmlns_invoice']);
            $invoice->setAttribute('xmlns:cac', $config['zatca_xmlns_cac']);
            $invoice->setAttribute('xmlns:cbc', $config['zatca_xmlns_cbc']);
            $invoice->setAttribute('xmlns:ext', $config['zatca_xmlns_ext']);
            $xml->appendChild($invoice);
            
            // UBL Extensions
            $ublExt = $xml->createElement('ext:UBLExtensions');
            $ublExtension = $xml->createElement('ext:UBLExtension');
            $ublExtension->appendChild($xml->createElement('ext:ExtensionURI', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades'));
            $ublExtension->appendChild($xml->createElement('ext:ExtensionContent'));
            $ublExt->appendChild($ublExtension);
            $invoice->appendChild($ublExt);
            
            // Basic invoice info
            $invoice->appendChild($xml->createElement('cbc:ID', htmlspecialchars($sale_data->sale_no)));
            $invoice->appendChild($xml->createElement('cbc:UUID', htmlspecialchars($uuid)));
            $invoice->appendChild($xml->createElement('cbc:IssueDate', date('Y-m-d', strtotime($sale_data->sale_date))));
            $invoice->appendChild($xml->createElement('cbc:IssueTime', date('H:i:s', strtotime($sale_data->date_time))));
            
            $typeCode = $xml->createElement('cbc:InvoiceTypeCode', $invoice_type);
            $typeCode->setAttribute('name', $invoice_type);
            $invoice->appendChild($typeCode);
            
            $invoice->appendChild($xml->createElement('cbc:DocumentCurrencyCode', $currency));
            $invoice->appendChild($xml->createElement('cbc:TaxCurrencyCode', $currency));
            
            // Previous invoice hash
            $prev_hash = $this->getPreviousInvoiceHash($sale_data->outlet_id);
            $invoice->appendChild($xml->createElement('cbc:PreviousInvoiceHash', $prev_hash));
            
            // Supplier (Seller)
            $this->addSupplierParty($xml, $invoice);
            
            // Customer (Buyer)
            $this->addCustomerParty($xml, $invoice, $sale_data);
            
            // Tax Total
            $this->addTaxTotal($xml, $invoice, $sale_data, $currency);
            
            // Monetary Total
            $this->addMonetaryTotal($xml, $invoice, $sale_data, $currency);
            
            // Invoice Lines
            $this->addInvoiceLines($xml, $invoice, $sale_data, $currency);
            
            return $xml->saveXML();
            
        } catch (Exception $e) {
            $this->logError('XML Generation Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add supplier party to XML
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $invoice Invoice element
     * @return void
     */
    private function addSupplierParty($xml, $invoice) {
        "use strict";
        $supplierParty = $xml->createElement('cac:AccountingSupplierParty');
        $party = $xml->createElement('cac:Party');
        
        // VAT Registration
        $partyId = $xml->createElement('cac:PartyIdentification');
        $id = $xml->createElement('cbc:ID', htmlspecialchars($this->zatca_outlet->vat_number));
        $id->setAttribute('schemeID', 'VAT');
        $partyId->appendChild($id);
        $party->appendChild($partyId);
        
        // Address
        $address = $xml->createElement('cac:PostalAddress');
        $address->appendChild($xml->createElement('cbc:StreetName', htmlspecialchars($this->zatca_outlet->address)));
        $address->appendChild($xml->createElement('cbc:CityName', $this->CI->config->item('zatca_default_city', 'zatca_config')));
        $address->appendChild($xml->createElement('cbc:PostalZone', htmlspecialchars($this->zatca_outlet->postal_code)));
        $country = $xml->createElement('cac:Country');
        $country->appendChild($xml->createElement('cbc:IdentificationCode', $this->CI->config->item('zatca_country_code', 'zatca_config')));
        $address->appendChild($country);
        $party->appendChild($address);
        
        // Tax Scheme
        $taxScheme = $xml->createElement('cac:PartyTaxScheme');
        $taxScheme->appendChild($xml->createElement('cbc:CompanyID', htmlspecialchars($this->zatca_outlet->vat_number)));
        $scheme = $xml->createElement('cac:TaxScheme');
        $scheme->appendChild($xml->createElement('cbc:ID', $this->CI->config->item('zatca_tax_scheme', 'zatca_config')));
        $taxScheme->appendChild($scheme);
        $party->appendChild($taxScheme);
        
        // Legal Entity
        $legalEntity = $xml->createElement('cac:PartyLegalEntity');
        $legalEntity->appendChild($xml->createElement('cbc:RegistrationName', htmlspecialchars($this->zatca_outlet->legal_name_en)));
        $party->appendChild($legalEntity);
        
        $supplierParty->appendChild($party);
        $invoice->appendChild($supplierParty);
    }
    
    /**
     * Add customer party to XML
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $invoice Invoice element
     * @param object $sale_data Sale data
     * @return void
     */
    private function addCustomerParty($xml, $invoice, $sale_data) {
        "use strict";
        $customerParty = $xml->createElement('cac:AccountingCustomerParty');
        $party = $xml->createElement('cac:Party');
        
        if (isset($sale_data->customer) && $sale_data->customer && $sale_data->customer->id != 1) {
            $address = $xml->createElement('cac:PostalAddress');
            $address->appendChild($xml->createElement('cbc:StreetName', htmlspecialchars($sale_data->customer->address ?: 'N/A')));
            $address->appendChild($xml->createElement('cbc:CityName', $this->CI->config->item('zatca_default_city', 'zatca_config')));
            $country = $xml->createElement('cac:Country');
            $country->appendChild($xml->createElement('cbc:IdentificationCode', $this->CI->config->item('zatca_country_code', 'zatca_config')));
            $address->appendChild($country);
            $party->appendChild($address);
            
            $legalEntity = $xml->createElement('cac:PartyLegalEntity');
            $legalEntity->appendChild($xml->createElement('cbc:RegistrationName', htmlspecialchars($sale_data->customer->name)));
            $party->appendChild($legalEntity);
        }
        
        $customerParty->appendChild($party);
        $invoice->appendChild($customerParty);
    }
    
    /**
     * Add tax total to XML
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $invoice Invoice element
     * @param object $sale_data Sale data
     * @param string $currency Currency code
     * @return void
     */
    private function addTaxTotal($xml, $invoice, $sale_data, $currency) {
        "use strict";
        $taxTotal = $xml->createElement('cac:TaxTotal');
        $taxAmount = $xml->createElement('cbc:TaxAmount', number_format($sale_data->vat, 2, '.', ''));
        $taxAmount->setAttribute('currencyID', $currency);
        $taxTotal->appendChild($taxAmount);
        
        $taxSubtotal = $xml->createElement('cac:TaxSubtotal');
        $taxableAmount = $xml->createElement('cbc:TaxableAmount', number_format($sale_data->sub_total, 2, '.', ''));
        $taxableAmount->setAttribute('currencyID', $currency);
        $taxSubtotal->appendChild($taxableAmount);
        
        $taxAmt = $xml->createElement('cbc:TaxAmount', number_format($sale_data->vat, 2, '.', ''));
        $taxAmt->setAttribute('currencyID', $currency);
        $taxSubtotal->appendChild($taxAmt);
        
        $taxCategory = $xml->createElement('cac:TaxCategory');
        $taxCategory->appendChild($xml->createElement('cbc:ID', 'S'));
        $taxCategory->appendChild($xml->createElement('cbc:Percent', '15.00'));
        $scheme = $xml->createElement('cac:TaxScheme');
        $scheme->appendChild($xml->createElement('cbc:ID', $this->CI->config->item('zatca_tax_scheme', 'zatca_config')));
        $taxCategory->appendChild($scheme);
        $taxSubtotal->appendChild($taxCategory);
        
        $taxTotal->appendChild($taxSubtotal);
        $invoice->appendChild($taxTotal);
    }
    
    /**
     * Add monetary total to XML
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $invoice Invoice element
     * @param object $sale_data Sale data
     * @param string $currency Currency code
     * @return void
     */
    private function addMonetaryTotal($xml, $invoice, $sale_data, $currency) {
        "use strict";
        $monetary = $xml->createElement('cac:LegalMonetaryTotal');
        
        $lineExt = $xml->createElement('cbc:LineExtensionAmount', number_format($sale_data->sub_total, 2, '.', ''));
        $lineExt->setAttribute('currencyID', $currency);
        $monetary->appendChild($lineExt);
        
        $taxExc = $xml->createElement('cbc:TaxExclusiveAmount', number_format($sale_data->sub_total, 2, '.', ''));
        $taxExc->setAttribute('currencyID', $currency);
        $monetary->appendChild($taxExc);
        
        $taxInc = $xml->createElement('cbc:TaxInclusiveAmount', number_format($sale_data->total_payable, 2, '.', ''));
        $taxInc->setAttribute('currencyID', $currency);
        $monetary->appendChild($taxInc);
        
        $allowance = $xml->createElement('cbc:AllowanceTotalAmount', number_format($sale_data->total_discount_amount ?? 0, 2, '.', ''));
        $allowance->setAttribute('currencyID', $currency);
        $monetary->appendChild($allowance);
        
        $payable = $xml->createElement('cbc:PayableAmount', number_format($sale_data->total_payable, 2, '.', ''));
        $payable->setAttribute('currencyID', $currency);
        $monetary->appendChild($payable);
        
        $invoice->appendChild($monetary);
    }
    
    /**
     * Add invoice lines to XML
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $invoice Invoice element
     * @param object $sale_data Sale data
     * @param string $currency Currency code
     * @return void
     */
    private function addInvoiceLines($xml, $invoice, $sale_data, $currency) {
        "use strict";
        if (!isset($sale_data->items) || !$sale_data->items) {
            return;
        }
        
        foreach ($sale_data->items as $index => $item) {
            $line = $xml->createElement('cac:InvoiceLine');
            $line->appendChild($xml->createElement('cbc:ID', ($index + 1)));
            $line->appendChild($xml->createElement('cbc:InvoicedQuantity', number_format($item->qty, 2, '.', '')));
            
            $lineAmt = $xml->createElement('cbc:LineExtensionAmount', number_format($item->menu_price_with_discount, 2, '.', ''));
            $lineAmt->setAttribute('currencyID', $currency);
            $line->appendChild($lineAmt);
            
            $invoiceItem = $xml->createElement('cac:Item');
            $invoiceItem->appendChild($xml->createElement('cbc:Name', htmlspecialchars($item->menu_name)));
            
            $taxCategory = $xml->createElement('cac:ClassifiedTaxCategory');
            $taxCategory->appendChild($xml->createElement('cbc:ID', 'S'));
            $taxCategory->appendChild($xml->createElement('cbc:Percent', '15.00'));
            $scheme = $xml->createElement('cac:TaxScheme');
            $scheme->appendChild($xml->createElement('cbc:ID', $this->CI->config->item('zatca_tax_scheme', 'zatca_config')));
            $taxCategory->appendChild($scheme);
            $invoiceItem->appendChild($taxCategory);
            $line->appendChild($invoiceItem);
            
            $price = $xml->createElement('cac:Price');
            $priceAmt = $xml->createElement('cbc:PriceAmount', number_format($item->menu_unit_price, 2, '.', ''));
            $priceAmt->setAttribute('currencyID', $currency);
            $price->appendChild($priceAmt);
            $line->appendChild($price);
            
            $invoice->appendChild($line);
        }
    }
    
    /**
     * Sign invoice (Optimized)
     * 
     * @param string $xml_content XML content
     * @return array|null Signed data
     */
    private function signInvoice($xml_content) {
        "use strict";
        try {
            // Calculate hash
            $hash = hash($this->CI->config->item('zatca_hash_algorithm', 'zatca_config'), $xml_content);
            $hash_base64 = base64_encode(hex2bin($hash));
            
            // Get private key
            $private_key_resource = openssl_pkey_get_private($this->zatca_outlet->private_key);
            if (!$private_key_resource) {
                $this->logError('Invalid private key');
                return null;
            }
            
            // Sign
            $signature = '';
            openssl_sign($hash, $signature, $private_key_resource, $this->CI->config->item('zatca_signature_algorithm', 'zatca_config'));
            openssl_free_key($private_key_resource);
            
            $signature_base64 = base64_encode($signature);
            
            // Embed signature
            $signed_xml = $this->embedSignature($xml_content, $hash_base64, $signature_base64);
            
            return array(
                'hash' => $hash_base64,
                'signed_xml' => base64_encode($signed_xml)
            );
            
        } catch (Exception $e) {
            $this->logError('Signing Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Embed signature in XML (Optimized)
     * 
     * @param string $xml_content XML content
     * @param string $hash Hash value
     * @param string $signature Signature value
     * @return string Signed XML
     */
    private function embedSignature($xml_content, $hash, $signature) {
        "use strict";
        $xml = new DOMDocument();
        $xml->loadXML($xml_content);
        
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('ext', $this->CI->config->item('zatca_xmlns_ext', 'zatca_config'));
        $extContent = $xpath->query('//ext:ExtensionContent')->item(0);
        
        if ($extContent) {
            $config = $this->CI->config->item('zatca_config');
            
            $sig = $xml->createElementNS($config['zatca_xmlns_ds'], 'ds:Signature');
            $sig->setAttribute('Id', 'signature');
            
            $signedInfo = $xml->createElement('ds:SignedInfo');
            $canonMethod = $xml->createElement('ds:CanonicalizationMethod');
            $canonMethod->setAttribute('Algorithm', $config['zatca_canonicalization_method']);
            $signedInfo->appendChild($canonMethod);
            
            $sigMethod = $xml->createElement('ds:SignatureMethod');
            $sigMethod->setAttribute('Algorithm', $config['zatca_signature_method']);
            $signedInfo->appendChild($sigMethod);
            
            $ref = $xml->createElement('ds:Reference');
            $ref->setAttribute('URI', '');
            $transforms = $xml->createElement('ds:Transforms');
            $transform = $xml->createElement('ds:Transform');
            $transform->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
            $transforms->appendChild($transform);
            $ref->appendChild($transforms);
            
            $digestMethod = $xml->createElement('ds:DigestMethod');
            $digestMethod->setAttribute('Algorithm', $config['zatca_digest_method']);
            $ref->appendChild($digestMethod);
            $ref->appendChild($xml->createElement('ds:DigestValue', $hash));
            $signedInfo->appendChild($ref);
            
            $sig->appendChild($signedInfo);
            $sig->appendChild($xml->createElement('ds:SignatureValue', $signature));
            
            $keyInfo = $xml->createElement('ds:KeyInfo');
            $x509Data = $xml->createElement('ds:X509Data');
            $x509Data->appendChild($xml->createElement('ds:X509Certificate', $this->zatca_token->binary_security_token));
            $keyInfo->appendChild($x509Data);
            $sig->appendChild($keyInfo);
            
            $extContent->appendChild($sig);
        }
        
        return $xml->saveXML();
    }
    
    /**
     * Generate QR code (Optimized)
     * 
     * @param object $sale_data Sale data
     * @param string $uuid UUID
     * @return string Base64 QR code
     */
    private function generateQRCode($sale_data, $uuid) {
        "use strict";
        $config = $this->CI->config->item('zatca_config');
        $qr_data = '';
        
        $qr_data .= chr($config['zatca_qr_tag_seller_name']) . chr(strlen($this->zatca_outlet->legal_name_en)) . $this->zatca_outlet->legal_name_en;
        $qr_data .= chr($config['zatca_qr_tag_vat_number']) . chr(strlen($this->zatca_outlet->vat_number)) . $this->zatca_outlet->vat_number;
        
        $timestamp = date('Y-m-d\TH:i:s\Z', strtotime($sale_data->date_time));
        $qr_data .= chr($config['zatca_qr_tag_timestamp']) . chr(strlen($timestamp)) . $timestamp;
        
        $total = number_format($sale_data->total_payable, 2, '.', '');
        $qr_data .= chr($config['zatca_qr_tag_invoice_total']) . chr(strlen($total)) . $total;
        
        $vat = number_format($sale_data->vat, 2, '.', '');
        $qr_data .= chr($config['zatca_qr_tag_vat_amount']) . chr(strlen($vat)) . $vat;
        
        return base64_encode($qr_data);
    }
    
    /**
     * Get previous invoice hash (Optimized with single query)
     * 
     * @param int $outlet_id Outlet ID
     * @return string Previous hash
     */
    private function getPreviousInvoiceHash($outlet_id) {
        "use strict";
        $result = $this->CI->db->select('zatca_response')
                                ->from('tbl_sales')
                                ->where('outlet_id', $outlet_id)
                                ->where_in('zatca_status', array('reported', 'cleared'))
                                ->where('del_status', 'Live')
                                ->order_by('id', 'DESC')
                                ->limit(1)
                                ->get()
                                ->row();
        
        if ($result && $result->zatca_response) {
            $zatca_data = json_decode($result->zatca_response);
            if (isset($zatca_data->invoice_hash)) {
                return $zatca_data->invoice_hash;
            }
        }
        
        return $this->CI->config->item('zatca_default_previous_hash', 'zatca_config');
    }
    
    /**
     * Call ZATCA API (Optimized)
     * 
     * @param string $hash Invoice hash
     * @param string $uuid UUID
     * @param string $signed_xml Signed XML
     * @return array Response
     */
    private function callZatcaApi($hash, $uuid, $signed_xml) {
        "use strict";
        $result = array('success' => false, 'message' => '', 'data' => array());
        
        try {
            $config = $this->CI->config->item('zatca_config');
            
            // Get API URL
            $api_url = ($this->environment === 'production') 
                ? $config['zatca_production_reporting_api'] 
                : $config['zatca_sandbox_reporting_api'];
            
            // Prepare request
            $request_body = json_encode(array(
                'invoiceHash' => $hash,
                'uuid' => $uuid,
                'invoice' => $signed_xml
            ));
            
            $auth = base64_encode($this->zatca_token->binary_security_token . ':' . $this->zatca_token->secret);
            
            // cURL request
            $ch = curl_init($api_url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $request_body,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Accept-Language: ' . $config['zatca_api_language'],
                    'Authorization: Basic ' . $auth,
                    'Accept-Version: ' . $config['zatca_api_version']
                ),
                CURLOPT_SSL_VERIFYPEER => $config['zatca_ssl_verify_peer'],
                CURLOPT_SSL_VERIFYHOST => $config['zatca_ssl_verify_host'],
                CURLOPT_TIMEOUT => $config['zatca_api_timeout']
            ));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($curl_error) {
                $result['message'] = 'cURL Error: ' . $curl_error;
                return $result;
            }
            
            $response_data = json_decode($response);
            
            if ($http_code === 200 || $http_code === 201) {
                $result['success'] = true;
                $result['message'] = 'Invoice submitted successfully';
                $result['data'] = array(
                    'http_code' => $http_code,
                    'response' => $response_data,
                    'status' => 'reported',
                    'request_id' => isset($response_data->requestId) ? $response_data->requestId : ''
                );
            } else {
                $result['message'] = 'API Error: ' . ($response_data->message ?? 'Unknown error');
                $result['data'] = array(
                    'http_code' => $http_code,
                    'response' => $response_data,
                    'status' => 'failed'
                );
            }
            
        } catch (Exception $e) {
            $result['message'] = 'API Exception: ' . $e->getMessage();
            $this->logError('API Exception: ' . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Update sale with ZATCA data (Simple 2-column approach)
     * 
     * @param int $sale_id Sale ID
     * @param string $uuid UUID
     * @param string $hash Hash
     * @param string $signed_xml Signed XML
     * @param string $qr_code QR code
     * @param array $api_response API response
     * @return bool Success
     */
    private function updateSaleZatcaData($sale_id, $uuid, $hash, $signed_xml, $qr_code, $api_response) {
        "use strict";
        
        // Prepare complete ZATCA response data
        $zatca_data = array(
            'uuid' => $uuid,
            'invoice_hash' => $hash,
            'signed_invoice' => $signed_xml,
            'qr_code' => $qr_code,
            'submission_date' => date('Y-m-d H:i:s'),
            'api_response' => isset($api_response['data']['response']) ? $api_response['data']['response'] : null,
            'error_message' => $api_response['success'] ? null : $api_response['message']
        );
        
        // Determine status
        $status = $api_response['success'] ? $api_response['data']['status'] : 'failed';
        
        // Update with just 2 columns
        $update_data = array(
            'zatca_status' => $status,
            'zatca_response' => json_encode($zatca_data)
        );
        
        return $this->CI->db->where('id', $sale_id)->update('tbl_sales', $update_data);
    }
    
    /**
     * Log error message
     * 
     * @param string $message Error message
     * @return void
     */
    private function logError($message) {
        "use strict";
        if ($this->CI->config->item('zatca_enable_logging', 'zatca_config')) {
            log_message('error', 'ZATCA: ' . $message);
        }
    }
    
    /**
     * Generate Credit Note XML
     * 
     * @param object $refund_data Refund data
     * @param string $uuid Credit Note UUID
     * @param object $original_zatca Original invoice ZATCA data
     * @return string|null XML content
     */
    private function generateCreditNoteXML($refund_data, $uuid, $original_zatca) {
        "use strict";
        try {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = false;
            
            // Get config
            $config = $this->CI->config->item('zatca_config');
            $credit_note_type = isset($config['zatca_credit_note_type']) ? $config['zatca_credit_note_type'] : '0200';
            $currency = $config['zatca_currency_code'];
            
            // Root element (CreditNote instead of Invoice)
            $creditNote = $xml->createElement('CreditNote');
            $creditNote->setAttribute('xmlns', $config['zatca_xmlns_invoice']);
            $creditNote->setAttribute('xmlns:cac', $config['zatca_xmlns_cac']);
            $creditNote->setAttribute('xmlns:cbc', $config['zatca_xmlns_cbc']);
            $creditNote->setAttribute('xmlns:ext', $config['zatca_xmlns_ext']);
            $xml->appendChild($creditNote);
            
            // UBL Extensions
            $ublExt = $xml->createElement('ext:UBLExtensions');
            $ublExtension = $xml->createElement('ext:UBLExtension');
            $ublExtension->appendChild($xml->createElement('ext:ExtensionURI', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades'));
            $ublExtension->appendChild($xml->createElement('ext:ExtensionContent'));
            $ublExt->appendChild($ublExtension);
            $creditNote->appendChild($ublExt);
            
            // Basic credit note info
            $creditNote->appendChild($xml->createElement('cbc:ID', htmlspecialchars($refund_data->sale_no . '-CN')));
            $creditNote->appendChild($xml->createElement('cbc:UUID', htmlspecialchars($uuid)));
            $creditNote->appendChild($xml->createElement('cbc:IssueDate', date('Y-m-d', strtotime($refund_data->refund_date_time))));
            $creditNote->appendChild($xml->createElement('cbc:IssueTime', date('H:i:s', strtotime($refund_data->refund_date_time))));
            
            $typeCode = $xml->createElement('cbc:CreditNoteTypeCode', $credit_note_type);
            $typeCode->setAttribute('name', 'Credit Note');
            $creditNote->appendChild($typeCode);
            
            $creditNote->appendChild($xml->createElement('cbc:DocumentCurrencyCode', $currency));
            $creditNote->appendChild($xml->createElement('cbc:TaxCurrencyCode', $currency));
            
            // Reference to original invoice
            $discrepancyResponse = $xml->createElement('cac:DiscrepancyResponse');
            $discrepancyResponse->appendChild($xml->createElement('cbc:ReferenceID', htmlspecialchars($original_zatca->uuid)));
            $discrepancyResponse->appendChild($xml->createElement('cbc:ResponseCode', '0200'));
            $discrepancyResponse->appendChild($xml->createElement('cbc:Description', 'Credit Note for Refund'));
            $creditNote->appendChild($discrepancyResponse);
            
            // Billing Reference (link to original invoice)
            $billingReference = $xml->createElement('cac:BillingReference');
            $invoiceDocumentReference = $xml->createElement('cac:InvoiceDocumentReference');
            $invoiceDocumentReference->appendChild($xml->createElement('cbc:ID', htmlspecialchars($refund_data->sale_no)));
            $invoiceDocumentReference->appendChild($xml->createElement('cbc:UUID', htmlspecialchars($original_zatca->uuid)));
            $billingReference->appendChild($invoiceDocumentReference);
            $creditNote->appendChild($billingReference);
            
            // Previous invoice hash (chain of invoices)
            $prev_hash = $this->getPreviousInvoiceHash($refund_data->outlet_id);
            $creditNote->appendChild($xml->createElement('cbc:PreviousInvoiceHash', $prev_hash));
            
            // Supplier (Seller)
            $this->addSupplierParty($xml, $creditNote);
            
            // Customer (Buyer)
            $this->addCustomerParty($xml, $creditNote, $refund_data);
            
            // Tax Total (negative amounts for credit note)
            $this->addCreditNoteTaxTotal($xml, $creditNote, $refund_data, $currency);
            
            // Monetary Total (negative amounts for credit note)
            $this->addCreditNoteMonetaryTotal($xml, $creditNote, $refund_data, $currency);
            
            // Credit Note Lines
            $this->addCreditNoteLines($xml, $creditNote, $refund_data, $currency);
            
            return $xml->saveXML();
            
        } catch (Exception $e) {
            $this->logError('Credit Note XML Generation Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add tax total to Credit Note XML (negative amounts)
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $creditNote Credit Note element
     * @param object $refund_data Refund data
     * @param string $currency Currency code
     * @return void
     */
    private function addCreditNoteTaxTotal($xml, $creditNote, $refund_data, $currency) {
        "use strict";
        // Calculate refund tax (negative)
        $refund_tax = isset($refund_data->refund_tax) ? abs($refund_data->refund_tax) : 0;
        $refund_subtotal = isset($refund_data->refund_subtotal) ? abs($refund_data->refund_subtotal) : 0;
        
        $taxTotal = $xml->createElement('cac:TaxTotal');
        $taxAmount = $xml->createElement('cbc:TaxAmount', '-' . number_format($refund_tax, 2, '.', ''));
        $taxAmount->setAttribute('currencyID', $currency);
        $taxTotal->appendChild($taxAmount);
        
        $taxSubtotal = $xml->createElement('cac:TaxSubtotal');
        $taxableAmount = $xml->createElement('cbc:TaxableAmount', '-' . number_format($refund_subtotal, 2, '.', ''));
        $taxableAmount->setAttribute('currencyID', $currency);
        $taxSubtotal->appendChild($taxableAmount);
        
        $taxAmt = $xml->createElement('cbc:TaxAmount', '-' . number_format($refund_tax, 2, '.', ''));
        $taxAmt->setAttribute('currencyID', $currency);
        $taxSubtotal->appendChild($taxAmt);
        
        $taxCategory = $xml->createElement('cac:TaxCategory');
        $taxCategory->appendChild($xml->createElement('cbc:ID', 'S'));
        $taxCategory->appendChild($xml->createElement('cbc:Percent', '15.00'));
        $scheme = $xml->createElement('cac:TaxScheme');
        $scheme->appendChild($xml->createElement('cbc:ID', $this->CI->config->item('zatca_tax_scheme', 'zatca_config')));
        $taxCategory->appendChild($scheme);
        $taxSubtotal->appendChild($taxCategory);
        
        $taxTotal->appendChild($taxSubtotal);
        $creditNote->appendChild($taxTotal);
    }
    
    /**
     * Add monetary total to Credit Note XML (negative amounts)
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $creditNote Credit Note element
     * @param object $refund_data Refund data
     * @param string $currency Currency code
     * @return void
     */
    private function addCreditNoteMonetaryTotal($xml, $creditNote, $refund_data, $currency) {
        "use strict";
        $refund_subtotal = isset($refund_data->refund_subtotal) ? abs($refund_data->refund_subtotal) : 0;
        $refund_total = isset($refund_data->total_refund) ? abs($refund_data->total_refund) : 0;
        $refund_discount = isset($refund_data->refund_discount) ? abs($refund_data->refund_discount) : 0;
        
        $monetary = $xml->createElement('cac:LegalMonetaryTotal');
        
        $lineExt = $xml->createElement('cbc:LineExtensionAmount', '-' . number_format($refund_subtotal, 2, '.', ''));
        $lineExt->setAttribute('currencyID', $currency);
        $monetary->appendChild($lineExt);
        
        $taxExc = $xml->createElement('cbc:TaxExclusiveAmount', '-' . number_format($refund_subtotal, 2, '.', ''));
        $taxExc->setAttribute('currencyID', $currency);
        $monetary->appendChild($taxExc);
        
        $taxInc = $xml->createElement('cbc:TaxInclusiveAmount', '-' . number_format($refund_total, 2, '.', ''));
        $taxInc->setAttribute('currencyID', $currency);
        $monetary->appendChild($taxInc);
        
        if ($refund_discount > 0) {
            $allowance = $xml->createElement('cbc:AllowanceTotalAmount', '-' . number_format($refund_discount, 2, '.', ''));
            $allowance->setAttribute('currencyID', $currency);
            $monetary->appendChild($allowance);
        }
        
        $payable = $xml->createElement('cbc:PayableAmount', '-' . number_format($refund_total, 2, '.', ''));
        $payable->setAttribute('currencyID', $currency);
        $monetary->appendChild($payable);
        
        $creditNote->appendChild($monetary);
    }
    
    /**
     * Add credit note lines to XML
     * 
     * @param DOMDocument $xml XML document
     * @param DOMElement $creditNote Credit Note element
     * @param object $refund_data Refund data
     * @param string $currency Currency code
     * @return void
     */
    private function addCreditNoteLines($xml, $creditNote, $refund_data, $currency) {
        "use strict";
        if (!isset($refund_data->refund_items) || !$refund_data->refund_items) {
            return;
        }
        
        // Parse refund items from JSON if string
        $items = is_string($refund_data->refund_items) 
            ? json_decode($refund_data->refund_items) 
            : $refund_data->refund_items;
        
        if (!$items) {
            return;
        }
        
        foreach ($items as $index => $item) {
            $line = $xml->createElement('cac:CreditNoteLine');
            $line->appendChild($xml->createElement('cbc:ID', ($index + 1)));
            $line->appendChild($xml->createElement('cbc:CreditedQuantity', '-' . number_format($item->refund_qty, 2, '.', '')));
            
            $lineAmt = $xml->createElement('cbc:LineExtensionAmount', '-' . number_format(($item->price * $item->refund_qty) - ($item->discount ?? 0), 2, '.', ''));
            $lineAmt->setAttribute('currencyID', $currency);
            $line->appendChild($lineAmt);
            
            $invoiceItem = $xml->createElement('cac:Item');
            $invoiceItem->appendChild($xml->createElement('cbc:Name', htmlspecialchars($item->name)));
            
            $taxCategory = $xml->createElement('cac:ClassifiedTaxCategory');
            $taxCategory->appendChild($xml->createElement('cbc:ID', 'S'));
            $taxCategory->appendChild($xml->createElement('cbc:Percent', '15.00'));
            $scheme = $xml->createElement('cac:TaxScheme');
            $scheme->appendChild($xml->createElement('cbc:ID', $this->CI->config->item('zatca_tax_scheme', 'zatca_config')));
            $taxCategory->appendChild($scheme);
            $invoiceItem->appendChild($taxCategory);
            $line->appendChild($invoiceItem);
            
            $price = $xml->createElement('cac:Price');
            $priceAmt = $xml->createElement('cbc:PriceAmount', number_format($item->price, 2, '.', ''));
            $priceAmt->setAttribute('currencyID', $currency);
            $price->appendChild($priceAmt);
            $line->appendChild($price);
            
            $creditNote->appendChild($line);
        }
    }
    
    /**
     * Generate QR code for Credit Note
     * 
     * @param object $refund_data Refund data
     * @param string $uuid UUID
     * @return string Base64 QR code
     */
    private function generateCreditNoteQRCode($refund_data, $uuid) {
        "use strict";
        $config = $this->CI->config->item('zatca_config');
        $qr_data = '';
        
        $qr_data .= chr($config['zatca_qr_tag_seller_name']) . chr(strlen($this->zatca_outlet->legal_name_en)) . $this->zatca_outlet->legal_name_en;
        $qr_data .= chr($config['zatca_qr_tag_vat_number']) . chr(strlen($this->zatca_outlet->vat_number)) . $this->zatca_outlet->vat_number;
        
        $timestamp = date('Y-m-d\TH:i:s\Z', strtotime($refund_data->refund_date_time));
        $qr_data .= chr($config['zatca_qr_tag_timestamp']) . chr(strlen($timestamp)) . $timestamp;
        
        $total = number_format(abs($refund_data->total_refund), 2, '.', '');
        $qr_data .= chr($config['zatca_qr_tag_invoice_total']) . chr(strlen($total)) . $total;
        
        $vat = number_format(abs($refund_data->refund_tax ?? 0), 2, '.', '');
        $qr_data .= chr($config['zatca_qr_tag_vat_amount']) . chr(strlen($vat)) . $vat;
        
        return base64_encode($qr_data);
    }
    
    /**
     * Call ZATCA Clearance API (for Credit Notes)
     * 
     * @param string $hash Invoice hash
     * @param string $uuid UUID
     * @param string $signed_xml Signed XML
     * @return array Response
     */
    private function callZatcaClearanceApi($hash, $uuid, $signed_xml) {
        "use strict";
        $result = array('success' => false, 'message' => '', 'data' => array());
        
        try {
            $config = $this->CI->config->item('zatca_config');
            
            // Get Clearance API URL (Credit Notes use Clearance API)
            $api_url = ($this->environment === 'production') 
                ? $config['zatca_production_clearance_api'] 
                : $config['zatca_sandbox_clearance_api'];
            
            // Prepare request
            $request_body = json_encode(array(
                'invoiceHash' => $hash,
                'uuid' => $uuid,
                'invoice' => $signed_xml
            ));
            
            $auth = base64_encode($this->zatca_token->binary_security_token . ':' . $this->zatca_token->secret);
            
            // cURL request
            $ch = curl_init($api_url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $request_body,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Accept-Language: ' . $config['zatca_api_language'],
                    'Authorization: Basic ' . $auth,
                    'Accept-Version: ' . $config['zatca_api_version']
                ),
                CURLOPT_SSL_VERIFYPEER => $config['zatca_ssl_verify_peer'],
                CURLOPT_SSL_VERIFYHOST => $config['zatca_ssl_verify_host'],
                CURLOPT_TIMEOUT => $config['zatca_api_timeout']
            ));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($curl_error) {
                $result['message'] = 'cURL Error: ' . $curl_error;
                return $result;
            }
            
            $response_data = json_decode($response);
            
            if ($http_code === 200 || $http_code === 201) {
                $result['success'] = true;
                $result['message'] = 'Credit Note submitted successfully';
                $result['data'] = array(
                    'http_code' => $http_code,
                    'response' => $response_data,
                    'status' => 'cleared',
                    'request_id' => isset($response_data->requestId) ? $response_data->requestId : ''
                );
            } else {
                $result['message'] = 'API Error: ' . ($response_data->message ?? 'Unknown error');
                $result['data'] = array(
                    'http_code' => $http_code,
                    'response' => $response_data,
                    'status' => 'failed'
                );
            }
            
        } catch (Exception $e) {
            $result['message'] = 'API Exception: ' . $e->getMessage();
            $this->logError('Clearance API Exception: ' . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Update sale with ZATCA Credit Note data
     * 
     * @param int $sale_id Sale ID
     * @param string $uuid Credit Note UUID
     * @param string $hash Hash
     * @param string $signed_xml Signed XML
     * @param string $qr_code QR code
     * @param array $api_response API response
     * @param string $original_uuid Original invoice UUID
     * @return bool Success
     */
    private function updateSaleZatcaCreditNoteData($sale_id, $uuid, $hash, $signed_xml, $qr_code, $api_response, $original_uuid) {
        "use strict";
        
        // Prepare complete ZATCA credit note response data
        $zatca_data = array(
            'credit_note_uuid' => $uuid,
            'credit_note_hash' => $hash,
            'signed_credit_note' => $signed_xml,
            'credit_note_qr_code' => $qr_code,
            'submission_date' => date('Y-m-d H:i:s'),
            'original_invoice_uuid' => $original_uuid,
            'api_response' => isset($api_response['data']['response']) ? $api_response['data']['response'] : null,
            'error_message' => $api_response['success'] ? null : $api_response['message']
        );
        
        // Determine status
        $status = $api_response['success'] ? $api_response['data']['status'] : 'failed';
        
        // Update with credit note data (store in zatca_credit_note_response column if exists, otherwise in zatca_response)
        $update_data = array(
            'zatca_credit_note_status' => $status,
            'zatca_credit_note_response' => json_encode($zatca_data)
        );
        
        // Check if column exists, if not use zatca_response
        $columns = $this->CI->db->list_fields('tbl_sales');
        if (!in_array('zatca_credit_note_status', $columns)) {
            // Fallback: store in zatca_response with prefix
            $update_data = array(
                'zatca_status' => 'credit_note_' . $status,
                'zatca_response' => json_encode(array_merge($zatca_data, array('type' => 'credit_note')))
            );
        }
        
        return $this->CI->db->where('id', $sale_id)->update('tbl_sales', $update_data);
    }
    
    /**
     * Save error to database (when exception or failure occurs before API call)
     * 
     * @param int $sale_id Sale ID
     * @param string $error_message Error message
     * @param string $details Additional details
     * @return bool Success
     */
    private function saveErrorToDatabase($sale_id, $error_message, $details = '') {
        "use strict";
        
        try {
            // Prepare error data
            $zatca_data = array(
                'invoice_uuid' => null,
                'invoice_hash' => null,
                'signed_invoice' => null,
                'qr_code' => null,
                'submission_date' => date('Y-m-d H:i:s'),
                'api_response' => null,
                'error_message' => $error_message,
                'error_details' => $details
            );
            
            // Update with error status
            $update_data = array(
                'zatca_status' => 'failed',
                'zatca_response' => json_encode($zatca_data)
            );
            
            return $this->CI->db->where('id', $sale_id)->update('tbl_sales', $update_data);
            
        } catch (Exception $e) {
            $this->logError('Failed to save error to database: ' . $e->getMessage());
            return false;
        }
    }
}
