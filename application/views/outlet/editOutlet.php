<link rel="stylesheet" href="<?= base_url() ?>frequent_changing/css/custom_check_box.css">
<!-- Main content -->
<section class="main-content-wrapper">

    <section class="content-header">
        <h3 class="top-left-header">
            <?php
            $data_c = getLanguageManifesto();
            if(str_rot13($data_c[0])=="eriutoeri"){
                echo lang('edit_outlet');
            }else if(str_rot13($data_c[0])=="fgjgldkfg"){
                echo lang('outlet_setting');
            }
            ?>
        </h3>

    </section>



    <div class="box-wrapper">
        <div class="table-box">
            <!-- /.box-header -->
            <!-- form start -->
            <?php echo form_open(base_url('Outlet/addEditOutlet/' . $encrypted_id)); ?>
            <div class="box-body">
                <div class="row">
                    <?php
                    if(str_rot13($data_c[0])=="eriutoeri") {
                        ?>
                        <div class="col-sm-12 mb-2 col-md-3">
                            <div class="form-group">
                                <label><?php echo lang('outlet_code'); ?> <span
                                            class="required_star">*</span></label>
                                <input tabindex="1" autocomplete="off" type="text" name="outlet_code"
                                        class="form-control" onfocus="select();"
                                        placeholder="<?php echo lang('outlet_code'); ?>"
                                        value="<?php echo escape_output($outlet_information->outlet_code) ?>"/>
                            </div>
                            <?php if (form_error('outlet_code')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('outlet_code'); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('outlet_name'); ?> <span class="required_star">*</span></label>
                            <input tabindex="1" autocomplete="off" type="text" name="outlet_name" class="form-control" placeholder="<?php echo lang('outlet_name'); ?>" value="<?php echo escape_output($outlet_information->outlet_name); ?>">
                        </div>
                        <?php if (form_error('outlet_name')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('outlet_name'); ?>
                            </div>
                        <?php } ?>

                    </div>

                    <div class="col-sm-12 mb-2 col-md-3">

                        <div class="form-group">
                            <label><?php echo lang('phone'); ?> <span class="required_star">*</span></label>
                            <input tabindex="4" autocomplete="off" type="text" name="phone" class="form-control" placeholder="<?php echo lang('phone'); ?>" value="<?php echo escape_output($outlet_information->phone); ?>">
                        </div>
                        <?php if (form_error('phone')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('phone'); ?>
                            </div>
                        <?php } ?>

                    </div>
                    <div class="col-sm-12 mb-2 col-md-3">

                        <div class="form-group">
                            <label><?php echo lang('email'); ?> </label>
                            <input tabindex="4" autocomplete="off" type="text" name="email" class="form-control" placeholder="<?php echo lang('email'); ?>" value="<?php echo escape_output($outlet_information->email); ?>" />
                        </div>
                        <?php if (form_error('email')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('email'); ?>
                            </div>
                        <?php } ?>

                    </div>

                    <?php
                    $language_manifesto = $this->session->userdata('language_manifesto');
                    if(str_rot13($language_manifesto)=="eriutoeri"):
                        ?>
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('Active_Status'); ?> <span class="required_star">*</span></label>
                            <select class="form-control select2" name="active_status" id="active_status">
                                <option <?php echo isset($outlet_information->active_status) && $outlet_information->active_status=="active"?'selected':''?> value="active"><?php echo lang('Active'); ?></option>
                                <option <?php echo isset($outlet_information->active_status) && $outlet_information->active_status=="inactive"?'selected':''?> value="inactive"><?php echo lang('Inactive'); ?></option>
                            </select>
                        </div>
                        <?php if (form_error('active_status')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('active_status'); ?>
                            </div>
                        <?php } ?>
                    </div>

                        <div class="col-sm-12 mb-2 col-md-3">

                            <div class="form-group">
                                <label> <?php echo lang('Default_Waiter'); ?></label>
                                <select tabindex="2" class="form-control select2" name="default_waiter" id="default_waiter">
                                    <option value=""><?php echo lang('select'); ?></option>
                                    <?php
                                    foreach ($waiters as $value):
                                        if($value->designation=="Waiter"):
                                            ?>
                                            <option <?=($outlet_information->default_waiter==$value->id?'selected':'')?>  value="<?=$value->id?>"><?=$value->full_name?></option>
                                            <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                            <?php if (form_error('default_waiter')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('default_waiter'); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                    endif;
                    ?>

                    <div class="col-sm-12 mb-2 col-md-3">

                        <div class="form-group">
                            <label><?php echo lang('address'); ?> <span class="required_star">*</span></label>
                            <textarea tabindex="3" autocomplete="off" name="address" class="form-control" placeholder="<?php echo lang('address'); ?>"><?php echo escape_output($outlet_information->address); ?></textarea>
                        </div>
                        <?php if (form_error('address')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('address'); ?>
                            </div>
                        <?php } ?>

                    </div>
                        

                    <div class="mb-3 col-sm-12 col-md-3 col-lg-3">
                            <div class="form-group">
                                <label> <?php echo lang('online_order_module'); ?> </label>
                                <select tabindex="7" class="form-control select2" name="online_order_module"
                                        id="online_order_module">
                                        <option
                                        <?= isset($outlet_information) && $outlet_information->online_order_module== "2" ? 'selected' : '' ?>
                                            value="2"><?php echo lang('yes')?></option>
                                        <option
                                        <?= isset($outlet_information) && $outlet_information->online_order_module== "1" ? 'selected' : '' ?>
                                            value="1"><?php echo lang('no')?></option>
                                  
                                </select>
                            </div>
                            <?php if (form_error('online_order_module')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('online_order_module'); ?>
                                </div>
                            <?php } ?>
                        </div>
                </div>

               
               <?php
                // Decode ZATCA outlet JSON data
                $zatca_data = isset($outlet_information->zatca_outlet) && $outlet_information->zatca_outlet ? json_decode($outlet_information->zatca_outlet) : null;
                $is_zatca_configured = isset($zatca_data) && $zatca_data ? true : false;
                $is_zatca_enable = isset($outlet_information->is_zatca_enable) && $outlet_information->is_zatca_enable == 1 ? true : false;
                
                // Check if all ZATCA fields are filled
                $all_fields_filled = false;
                if ($zatca_data) {
                    $all_fields_filled = (
                        !empty($zatca_data->legal_name_en) &&
                        !empty($zatca_data->legal_name_ar) &&
                        !empty($zatca_data->vat_number) &&
                        !empty($zatca_data->cr_number) &&
                        !empty($zatca_data->postal_code) &&
                        !empty($zatca_data->address)
                    );
                }
                ?>
               
               <!-- ZATCA Phase-2 Configuration Section -->
                <div class="row my-3">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">ZATCA Phase-2 Configuration <small class="text-muted">(Saudi E-Invoicing)</small></h6>
                            </div>
                            <div class="card-body">
                                <?php 
                                $zatca_connected = isset($outlet_information->zatca_token) && $outlet_information->zatca_token ? true : false;
                                ?>
                                
                                <!-- Enable/Disable Dropdown -->
                                <div class="row mb-3">
                                    <div class="col-sm-12 col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label><strong>Enable/Disable ZATCA</strong></label>
                                            <select class="form-control" id="zatca_enable_checkbox" name="is_zatca_enable" 
                                                    data-zatca-connected="<?php echo $zatca_connected ? '1' : '0'; ?>">
                                                <option value="0" <?php echo !$is_zatca_enable ? 'selected' : ''; ?>>Disable</option>
                                                <option value="1" <?php echo $is_zatca_enable ? 'selected' : ''; ?>>Enable</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <!-- ZATCA Fields Container (toggleable) -->
                                <div id="zatca_fields_container" style="display: <?php echo $is_zatca_enable ? 'block' : 'none'; ?>;">
                                <div class="row">
                                    <!-- Legal Name (English) -->
                                    <div class="col-sm-12 col-md-6 col-lg-3 mb-2">
                                        <div class="form-group">
                                            <label>Legal Name (English) <span class="zatca-required-star">*</span></label>
                                            <input tabindex="20" autocomplete="off" type="text" name="zatca_outlet[legal_name_en]" 
                                                   id="zatca_legal_name_en"
                                                   class="form-control zatca-field" 
                                                   placeholder="Legal Name (English)" 
                                                   value="<?php echo isset($zatca_data->legal_name_en) ? escape_output($zatca_data->legal_name_en) : ''; ?>">
                                            <?php if (form_error('zatca_outlet[legal_name_en]')) { ?>
                                                <div class="callout callout-danger my-2">
                                                    <small ><?php echo form_error('zatca_outlet[legal_name_en]'); ?></small>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Legal Name (Arabic) -->
                                    <div class="col-sm-12 col-md-6 col-lg-3 mb-2">
                                        <div class="form-group">
                                            <label>Legal Name (Arabic) <span class="zatca-required-star">*</span></label>
                                            <input tabindex="21" autocomplete="off" type="text" name="zatca_outlet[legal_name_ar]" 
                                                   id="zatca_legal_name_ar"
                                                   class="form-control zatca-field" 
                                                   placeholder="الاسم القانوني (عربي)" 
                                                   dir="rtl"
                                                   value="<?php echo isset($zatca_data->legal_name_ar) ? escape_output($zatca_data->legal_name_ar) : ''; ?>">
                                            <?php if (form_error('zatca_outlet[legal_name_ar]')) { ?>
                                                <div class="callout callout-danger my-2">
                                                    <small ><?php echo form_error('zatca_outlet[legal_name_ar]'); ?></small>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- VAT Number -->
                                    <div class="col-sm-12 col-md-6 col-lg-3 mb-2">
                                        <div class="form-group">
                                            <label>VAT Number <span class="zatca-required-star">*</span></label>
                                            <input tabindex="22" autocomplete="off" type="number" name="zatca_outlet[vat_number]" 
                                                   id="zatca_vat_number"
                                                   class="form-control zatca-field" 
                                                   placeholder="VAT Number (15 digits)" 
                                                   maxlength="15"
                                                   value="<?php echo isset($zatca_data->vat_number) ? escape_output($zatca_data->vat_number) : ''; ?>">
                                            <?php if (form_error('zatca_outlet[vat_number]')) { ?>
                                                <div class="callout callout-danger my-2">
                                                    <small ><?php echo form_error('zatca_outlet[vat_number]'); ?></small>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- CR Number -->
                                    <div class="col-sm-12 col-md-6 col-lg-3 mb-2">
                                        <div class="form-group">
                                            <label>CR Number <span class="zatca-required-star">*</span></label>
                                            <input tabindex="23" autocomplete="off" type="number" name="zatca_outlet[cr_number]" 
                                                   id="zatca_cr_number"
                                                   class="form-control zatca-field" 
                                                   placeholder="Commercial Registration Number" 
                                                   value="<?php echo isset($zatca_data->cr_number) ? escape_output($zatca_data->cr_number) : ''; ?>">
                                            <?php if (form_error('zatca_outlet[cr_number]')) { ?>
                                                <div class="callout callout-danger my-2">
                                                    <small ><?php echo form_error('zatca_outlet[cr_number]'); ?></small>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Postal Code -->
                                    <div class="col-sm-12 col-md-6 col-lg-3 mb-2">
                                        <div class="form-group">
                                            <label>Postal Code <span class="zatca-required-star">*</span></label>
                                            <input tabindex="24" autocomplete="off" type="text" name="zatca_outlet[postal_code]" 
                                                   id="zatca_postal_code"
                                                   class="form-control zatca-field" 
                                                   placeholder="Postal Code" 
                                                   value="<?php echo isset($zatca_data->postal_code) ? escape_output($zatca_data->postal_code) : ''; ?>">
                                            <?php if (form_error('zatca_outlet[postal_code]')) { ?>
                                                <div class="callout callout-danger my-2">
                                                    <small ><?php echo form_error('zatca_outlet[postal_code]'); ?></small>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="col-sm-12 col-md-12 col-lg-9 mb-2">
                                        <div class="form-group">
                                            <label>Address <span class="zatca-required-star">*</span></label>
                                            <textarea tabindex="25" autocomplete="off" name="zatca_outlet[address]" 
                                                      id="zatca_address"
                                                      class="form-control zatca-field" 
                                                      rows="3"
                                                      placeholder="Full Address"><?php echo isset($zatca_data->address) ? escape_output($zatca_data->address) : ''; ?></textarea>
                                            <?php if (form_error('zatca_outlet[address]')) { ?>
                                                <div class="callout callout-danger my-2">
                                                    <small ><?php echo form_error('zatca_outlet[address]'); ?></small>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- ZATCA Status and Connection Information (Bottom Section) -->
                                <div class="row mt-4">
                                    <!-- ZATCA Status (Top Left) -->
                                    <div class="col-sm-12 mb-3">
                                        <label class="d-block mb-2"><strong>ZATCA Status</strong></label>
                                        <?php if ($zatca_connected): ?>
                                            <span class="badge badge-success zatca-status-connected">
                                                <i class="fa fa-check-circle"></i> Connected
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary zatca-status-not-connected">
                                                <i class="fa fa-times-circle"></i> Not Connected
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Connection Information (Full Width) -->
                                    <div class="col-sm-12 mb-3">
                                        <?php if ($zatca_connected && $zatca_data): ?>
                                        <div class="alert alert-success mb-0 zatca-connection-info">
                                            <h6 class="mb-2"><i class="fa fa-check-circle"></i> Connection Information</h6>
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4 mb-2">
                                                    <strong>Device Serial:</strong><br>
                                                    <?php echo isset($zatca_data->device_serial) ? escape_output($zatca_data->device_serial) : 'N/A'; ?>
                                                </div>
                                                <div class="col-sm-12 col-md-4 mb-2">
                                                    <strong>Connected Date:</strong><br>
                                                    <?php echo isset($zatca_data->connected_date) ? escape_output($zatca_data->connected_date) : 'N/A'; ?>
                                                </div>
                                                <div class="col-sm-12 col-md-4 mb-2">
                                                    <strong>Certificate Status:</strong><br>
                                                    Active
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-secondary mb-0 zatca-no-connection">
                                            <p class="mb-0"><i class="fa fa-info-circle"></i> No connection information available. Please connect to ZATCA.</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                </div>
                                <!-- End ZATCA Fields Container -->
                                
                            </div>
                            <div class="card-footer zatca-card-footer">
                                <!-- Connect ZATCA Button (shows when not connected and all fields filled) -->
                                <button type="submit" name="connect_zatca" value="connect_zatca" id="connect_zatca_btn" class="btn bg-blue-btn zatca-connect-btn">
                                    <i class="fa fa-plug"></i>  Connect ZATCA
                                </button>
                                
                                <!-- Reconnect Button (shows when already connected and checkbox is checked) -->
                                <?php if ($zatca_connected): ?>
                                <button type="submit" name="connect_zatca" value="connect_zatca" id="reconnect_zatca_btn" class="btn btn-default" style="display: <?php echo $is_zatca_enable ? 'inline-block' : 'none'; ?>;">
                                    <i class="fa fa-refresh"></i> Reconnect ZATCA
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $language_manifesto = $this->session->userdata('language_manifesto');
                if(str_rot13($language_manifesto)=="eriutoeri"):
                ?>

                <div class="row my-3">
                    <div class="col-sm-6 col-md-12">
                        <div class="form-group">
                            <h6><?php echo lang('tooltip_txt_26'); ?></h6>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-sm-6 col-md-12">
                        <label class="container txt_48"> <?php echo lang('select_all'); ?>
                            <input class="checkbox_userAll" type="checkbox" id="checkbox_userAll">
                            <span class="checkmark"></span>
                        </label>
                        <b class="pull-right info_red"><?php echo lang('order_type_details'); ?></b>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <?php
                    foreach ($items as $item) {
                        $checked = '';
                        $new_id = $item->id;
                        if (isset($selected_modules_arr)):
                            foreach ($selected_modules_arr as $uma) {
                                if (in_array($new_id, $selected_modules_arr)) {
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }
                            }
                        endif;
                        $previous_price = (array)json_decode($outlet_information->food_menu_prices);
                        $sale_price_tmp = isset($previous_price["tmp".$item->id]) && $previous_price["tmp".$item->id]?$previous_price["tmp".$item->id]:'';

                        $dine_ta_price = $item->sale_price;
                        $sale_ta_price = $item->sale_price_take_away;
                        $sale_de_price = $item->sale_price_delivery;

                        if(isset($sale_price_tmp) && $sale_price_tmp){
                            $sale_price = explode("||",$sale_price_tmp);
                            $dine_ta_price = isset($sale_price[0]) && $sale_price[0]?$sale_price[0]:$item->sale_price;
                            $sale_ta_price = isset($sale_price[1]) && $sale_price[1]?$sale_price[1]:$item->sale_price_take_away;
                            $sale_de_price = isset($sale_price[2]) && $sale_price[2]?$sale_price[2]:$item->sale_price_delivery;
                        }

                        ?>
                        <div class="col-sm-12 col-md-3 mb-2">
                            <div class="border_custom">
                            <label class="container txt_47" for="checker_<?php echo escape_output($item->id)?>"><?="<b>".getParentNameTemp($item->parent_id).(isset($item->name) && $item->name?''.$item->name.'':'')."</b>"?>
                                <input class="checkbox_user child_class" id="checker_<?php echo escape_output($item->id)?>"  <?=$checked?> data-name="<?php echo str_replace(' ', '_', $item->name)?>" value="<?=$item->id?>" type="checkbox" name="item_check[]">
                                <span class="checkmark"></span>
                            </label>
                            <div class="form-group outlet-price-field">
                                <label class="txt_outlet_1"><?php echo lang('price'); ?><?php echo lang('DI'); ?></label>
                                <input  type="text" value="<?php echo escape_output($dine_ta_price)?>" name="price_<?php echo escape_output($item->id)?>" placeholder="<?php echo lang('price');?><?php echo lang('DI'); ?>" onfocus="select()" class="txt_21 form-control">
                            </div>
                            <div class="form-group outlet-price-field">
                                <label class="txt_outlet_1"><?php echo lang('price'); ?><?php echo lang('TA'); ?></label>
                                <input  type="text" value="<?php echo escape_output($sale_ta_price)?>" name="price_ta_<?php echo escape_output($item->id)?>" placeholder="<?php echo lang('price');?><?php echo lang('TA'); ?>" onfocus="select()" class="txt_21 form-control">
                            </div>
                        <?php if(!sizeof($deliveryPartners)):?>
                            <div class="form-group outlet-price-field">
                                <label class="txt_outlet_1"><?php echo lang('price'); ?><?php echo lang('De'); ?></label>
                                <input  type="text" value="<?php echo escape_output($sale_de_price)?>" name="price_de_<?php echo escape_output($item->id)?>" placeholder="<?php echo lang('price');?><?php echo lang('De'); ?>" onfocus="select()" class="form-control txt_21">
                            </div>
                        <?php else:?>
                            <label class="margin_top_de_price"><?php echo lang('price'); ?> <?php echo lang('De'); ?></label>
                                <div class="form-group  outlet-price-field">

                                    <table class="txt_21 margin_left_de_price">
                                        <tbody>
                                        <?php
                                        $delivery_price = (array)json_decode($outlet_information->delivery_price);
                                        foreach ($deliveryPartners as $value):
                                            $delivery_price_value = (array)json_decode(isset($delivery_price["index_".$item->id]) && $delivery_price["index_".$item->id]?$delivery_price["index_".$item->id]:'');
                                            $dl_price = isset($delivery_price_value["index_".$value->id]) && $delivery_price_value["index_".$value->id]?$delivery_price_value["index_".$value->id]:'';
                                            if(!$dl_price){
                                                $dl_price = $item->sale_price;
                                            }
                                            ?>
                                            <tr>
                                                    <td class="txt_21_50"><?php echo escape_output($value->name)?>
                                                </td>
                                                <td class="txt_21_50">
                                                    <input type="hidden" name="delivery_person<?=$item->id?>[]" value="<?php echo escape_output($value->id)?>">
                                                    <input tabindex="4" type="text" onfocus="this.select();"
                                                            name="sale_price_delivery_json<?=$item->id?>[]" class="margin_top_9 form-control integerchk check_required"
                                                            placeholder="<?php echo lang('sale_price'); ?> (<?php echo lang('delivery'); ?>)"
                                                            value="<?php echo escape_output($dl_price); ?>"></td>
                                            </tr>
                                        <?php endforeach;?>
                                        </tbody>
                                    </table>
                                </div>
                        <?php endif;?>
                            <br>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
                endif;
                ?>
            </div>
            <!-- /.box-body -->
            <?php
            $data_c = getLanguageManifesto();
            ?>
            <div class="box-footer">
                <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                    <i data-feather="upload"></i>
                    <?php echo lang('submit'); ?>
                </button>
            
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Outlet/outlets">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</section>

<script src="<?php echo base_url(); ?>frequent_changing/js/edit_outlet.js"></script>