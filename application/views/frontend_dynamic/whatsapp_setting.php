<!-- Main content -->
<section class="main-content-wrapper">

    <?php
    if ($this->session->flashdata('exception')) {

        echo '<section class="alert-wrapper"><div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>
    <?php
    if ($this->session->flashdata('exception_1')) {

        echo '<section class="alert-wrapper"><div class="alert alert-danger alert-dismissible"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception_1'));unset($_SESSION['exception_1']);
        echo '</p></div></div></section>';
    }
    ?>
    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('whatsapp_setting'); ?> <small class="color_red">(<?php echo lang('twilio_whatsapp_setup'); ?>)</small>
        </h3>
    </section>

    <div class="box-wrapper">
        <div class="table-box">
            <!-- /.box-header -->
            <!-- form start -->
            <?php
                $company_id = $this->session->userdata('company_id');
            $company = isset($company_info) ? $company_info : getMainCompany();
            $whatsappSettings = isset($company->whatsapp_settings) && $company->whatsapp_settings?json_decode($company->whatsapp_settings):'';
            ?>
            <?php echo form_open(base_url() . 'Frontend/whatsappSetting/'.(isset($company_id) && $company_id?$company_id:''), $arrayName = array('id' => 'add_whatsapp_setting','enctype'=>'multipart/form-data')) ?>
            <div class="box-body">
        
                <div class="row">
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('enable_status'); ?> </label>
                            <select class="form-control select2 width_100_p" name="enable_status" id="enable_status">
                                <option <?php echo isset($whatsappSettings) && $whatsappSettings->enable_status=="0"?'selected':''?> <?php echo set_select('enable_status', "0"); ?>  value="0"><?php echo lang('disable'); ?></option>
                                <option  <?php echo isset($whatsappSettings) && $whatsappSettings->enable_status=="1"?'selected':''?>   <?php echo set_select('enable_status', "1"); ?>   value="1"><?php echo lang('enable'); ?></option>
                            </select>
                        </div>
                        <?php if (form_error('enable_status')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('enable_status'); ?>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <div class="clearfix"></div>
                    <div class="col-sm-12 mb-2 col-md-6">
                        <div class="form-group">
                            <label><?php echo lang('account_sid'); ?> <span class="required_star">*</span></label>
                            <input type="text" name="account_sid" placeholder="<?php echo lang('account_sid'); ?>"  value="<?php echo isset($whatsappSettings) && $whatsappSettings->account_sid?$whatsappSettings->account_sid:set_value('account_sid')?>" id="account_sid" class="form-control">
                            <small class="form-text text-muted"><?php echo lang('twilio_account_sid_help'); ?></small>
                        </div>
                        <?php if (form_error('account_sid')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('account_sid'); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-sm-12 mb-2 col-md-6">
                        <div class="form-group">
                            <label><?php echo lang('auth_token'); ?> <span class="required_star">*</span></label>
                            <input type="password" name="auth_token" value="<?php echo isset($whatsappSettings) && $whatsappSettings->auth_token?$whatsappSettings->auth_token:set_value('auth_token')?>"  placeholder="<?php echo lang('auth_token'); ?>" id="auth_token" class="form-control">
                            <small class="form-text text-muted"><?php echo lang('twilio_auth_token_help'); ?></small>
                        </div>
                        <?php if (form_error('auth_token')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('auth_token'); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-sm-12 mb-2 col-md-6">
                        <div class="form-group">
                            <label><?php echo lang('whatsapp_number'); ?> <span class="required_star">*</span></label>
                            <input type="text" name="whatsapp_number" value="<?php echo isset($whatsappSettings) && $whatsappSettings->whatsapp_number?$whatsappSettings->whatsapp_number:set_value('whatsapp_number')?>" placeholder="<?php echo lang('whatsapp_number'); ?>" id="whatsapp_number" class="form-control">
                            <small class="form-text text-muted"><?php echo lang('twilio_whatsapp_number_help'); ?></small>
                        </div>
                        <?php if (form_error('whatsapp_number')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('whatsapp_number'); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                    <i data-feather="upload"></i>
                    <?php echo lang('submit'); ?>
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    
</section>

