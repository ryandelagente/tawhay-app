 (function() {
    let isVariationProduct = parseInt($("#is_variation_product").val(), 10) === 1;
    let parentId = $("#variation_parent_id").val();
    let baseUrl = $("#base_url").val();
    let precision = $("#precision").val() || 2;

    if (!isVariationProduct || !parentId) return;

    let $select = $("#variation_select");
    let $priceEl = $("#details_item_price");
    let $btn = $("#btn_add_to_cart_details");
    let $qtyInput = $("#item_details_qty");
    let $caloriesWrapper = $("#calories_info_wrapper");
    let $caloriesValue = $("#variation_calories_value");
    let pleaseSelectMsg = $("#please_select_variation_msg").val() || "Please select a variation";

    $select.append("<option value=''>Loading...</option>");

    let btnEl = document.getElementById("btn_add_to_cart_details");
    if (btnEl) {
      btnEl.addEventListener("click", function(e) {
        if (!$select.val() || $select.val() === "") {
          e.preventDefault();
          e.stopImmediatePropagation();
          if (typeof toastr !== "undefined") toastr.error(pleaseSelectMsg, "");
          return false;
        }
      }, true);
    }

    $.ajax({
      type: "POST",
      url: baseUrl + "Frontend/getVariationsByParentId",
      data: { parent_id: parentId },
      dataType: "json"
    }).done(function(res) {
      $select.find("option").remove();
      $select.append("<option value=''>Select...</option>");
      if (res.status === "success" && res.variations && res.variations.length) {
        res.variations.forEach(function(v) {
          let priceStr = parseFloat(v.sale_price).toFixed(precision);
          let cal = (v.calories !== undefined && v.calories !== null && v.calories !== '') ? String(v.calories) : '';
          $select.append("<option value='" + v.id + "' data-price='" + v.sale_price + "' data-tax='" + (v.tax_information || '[]') + "' data-calories='" + cal.replace(/'/g, "&#39;") + "'>" + (v.display_name || (res.parent_name + " " + v.name)) + "</option>");
          if (window.items) {
            let existing = window.items.filter(function(it) { return String(it.item_id) === String(v.id); });
            if (existing.length === 0) {
              window.items.push({
                item_id: String(v.id),
                parent_id: String(v.parent_id || parentId),
                product_type: String(v.product_type || "1"),
                item_name: v.display_name || (res.parent_name + " " + v.name),
                alternative_name: v.alternative_name || "",
                price: priceStr,
                tax_information: v.tax_information || "[]",
                vat_percentage: "0"
              });
            }
          }
        });
        setTimeout(function() { updatePriceAndTotal(); }, 0);
      }
    }).fail(function() {
      $select.find("option").remove();
      $select.append("<option value=''>Error loading options</option>");
    });

    function formatAmt(num) {
      return parseFloat(num).toFixed(precision);
    }

    function updatePriceAndTotal() {
      let opt = $select.find("option:selected");
      let val = opt.val();
      if (!val) {
        $btn.attr("data_single_order_id", "");
        $qtyInput.attr("class", "item_details_qty_" + parentId);
        $(".show_total_amount").text(formatAmt(0));
        if ($caloriesWrapper.length) { $caloriesWrapper.hide(); $caloriesValue.text(""); }
        return;
      }
      let price = parseFloat(opt.data("price")) || 0;
      let calories = opt.data("calories");
      $priceEl.attr("data-price", price).text(formatAmt(price));
      $btn.attr("data_single_order_id", val);
      $qtyInput.attr("class", "item_details_qty_" + val);
      let qty = parseInt($qtyInput.val(), 10) || 1;
      $(".show_total_amount").text(formatAmt(price * qty));
      if ($caloriesWrapper.length) {
        if (calories !== undefined && calories !== null && calories !== '') {
          $caloriesValue.text(calories);
          $caloriesWrapper.show();
        } else {
          $caloriesValue.text("");
          $caloriesWrapper.hide();
        }
      }
    }

    $select.on("change", function() {
      updatePriceAndTotal();
    });

    $(document).on("keyup change", "#item_details_qty", function() {
      let opt = $select.find("option:selected");
      let price = opt.length && opt.val() ? parseFloat(opt.data("price")) : parseFloat($priceEl.attr("data-price")) || 0;
      let qty = parseInt($(this).val(), 10) || 1;
      $(".show_total_amount").text(formatAmt(price * qty));
    });
  })(); 