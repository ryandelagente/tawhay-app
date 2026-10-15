
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
            <?php echo lang('whatsapp_setting'); ?>
        </h3>

    </section>

       <!-- left column -->
       <div class="col-md-12">
            <div class="table-box">
                <!-- /.box-header -->
                <!-- form start -->
                <?php echo form_open(base_url() . 'Frontend/whatsappSetting/'.(isset($company) && $company->id?$company->id:''), $arrayName = array('id' => 'add_whitelabel','enctype'=>'multipart/form-data')) ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo lang('WhatsApp Service Provider'); ?> <span class="required_star">*</span></label>
                                <select name="whatsapp_service_provider" id="whatsapp_service_provider" class="form-control select2 whatsapp_service_provider">
                                    <option value="0" <?=(!isset($company->whatsapp_service_provider) || $company->whatsapp_service_provider=="0" || $company->whatsapp_service_provider==0 || empty($company->whatsapp_service_provider)?'selected':'')?>><?php echo lang('disable'); ?></option>
                                    <option value="1" <?=(isset($company->whatsapp_service_provider) && $company->whatsapp_service_provider=="1"?'selected':'')?>><?php echo lang('Twilio'); ?></option>
                                    <option value="2" <?=(isset($company->whatsapp_service_provider) && $company->whatsapp_service_provider=="2"?'selected':'')?>><?php echo lang('RCSoft'); ?></option>
                                </select>
                            </div>
                            <?php if (form_error('whatsapp_service_provider')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('whatsapp_service_provider'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="clearfix">&nbsp;</div>
                    
                    <!-- Twilio Settings (Provider 1) -->
                    <div class="row div_twilio_settings" style="<?=(!isset($company->whatsapp_service_provider) || $company->whatsapp_service_provider=="0" || $company->whatsapp_service_provider==0 || empty($company->whatsapp_service_provider) || (isset($company->whatsapp_service_provider) && $company->whatsapp_service_provider=="2")?'display:none;':'')?>">
                        <div class="col-md-12">
                            <h4><?php echo lang('Twilio Settings'); ?></h4>
                        </div>
                        <?php 
                        $whatsapp_settings = isset($company->whatsapp_settings) && $company->whatsapp_settings ? json_decode($company->whatsapp_settings) : '';
                        ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo lang('account_sid'); ?> <span class="required_star">*</span></label>
                                <input type="text" name="account_sid" value="<?=(isset($whatsapp_settings->account_sid) && $whatsapp_settings->account_sid?escape_output($whatsapp_settings->account_sid):set_value('account_sid'))?>" placeholder="<?php echo lang('Account SID'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo lang('auth_token'); ?> <span class="required_star">*</span></label>
                                <input type="text" name="auth_token" value="<?=(isset($whatsapp_settings->auth_token) && $whatsapp_settings->auth_token?escape_output($whatsapp_settings->auth_token):set_value('auth_token'))?>" placeholder="<?php echo lang('Auth Token'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo lang('whatsapp_number'); ?> <span class="required_star">*</span></label>
                                <input type="text" name="whatsapp_number" value="<?=(isset($whatsapp_settings->whatsapp_number) && $whatsapp_settings->whatsapp_number?escape_output($whatsapp_settings->whatsapp_number):set_value('whatsapp_number'))?>" placeholder="<?php echo lang('WhatsApp Number'); ?>" class="form-control">
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- RCSoft Settings (Provider 2) -->
                    <div class="row div_rcsoft_settings" style="<?=(!isset($company->whatsapp_service_provider) || $company->whatsapp_service_provider=="0" || $company->whatsapp_service_provider==0 || empty($company->whatsapp_service_provider) || (isset($company->whatsapp_service_provider) && $company->whatsapp_service_provider=="1")?'display:none;':'')?>">
                        <div class="col-md-12">
                            <h4><?php echo lang('RCSoft Settings'); ?></h4>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo lang('App Key'); ?> <span class="required_star">*</span></label>
                                <input type="text" name="whatsapp_app_key" value="<?=(isset($company->whatsapp_app_key) && $company->whatsapp_app_key?escape_output($company->whatsapp_app_key):set_value('whatsapp_app_key'))?>" placeholder="<?php echo lang('App Key'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo lang('Auth Key'); ?> <span class="required_star">*</span></label>
                                <input type="text" name="whatsapp_authkey" value="<?=(isset($company->whatsapp_authkey) && $company->whatsapp_authkey?escape_output($company->whatsapp_authkey):set_value('whatsapp_authkey'))?>" placeholder="<?php echo lang('Auth Key'); ?>" class="form-control">
                            </div>
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
 
<script type="text/javascript">
$(document).ready(function() {
    // Handle provider change
    $('#whatsapp_service_provider').on('change', function() {
        var provider = $(this).val();
        if (provider == '0' || provider == '' || provider == null) {
            // Disable - hide all settings
            $('.div_twilio_settings').hide();
            $('.div_rcsoft_settings').hide();
        } else if (provider == '1') {
            // Twilio
            $('.div_twilio_settings').show();
            $('.div_rcsoft_settings').hide();
        } else if (provider == '2') {
            // RCSoft
            $('.div_twilio_settings').hide();
            $('.div_rcsoft_settings').show();
        }
    });
    
    // Trigger on page load
    $('#whatsapp_service_provider').trigger('change');
});
</script>