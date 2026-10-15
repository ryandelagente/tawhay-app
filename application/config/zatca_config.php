<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ZATCA Configuration File
 * 
 * Centralized configuration for ZATCA Phase-2 E-Invoice integration
 * All ZATCA API endpoints, settings, and constants are defined here
 * 
 * @package    iRestora PLUS
 * @subpackage Config
 * @category   ZATCA Integration
 * @author     Door Soft
 * @version    1.0.0
 */

// ==============================================================
// ZATCA API ENDPOINTS
// ==============================================================

/**
 * ZATCA Sandbox Environment URLs
 */
$config['zatca_sandbox_compliance_api'] = 'https://gw-apic-gov.gazt.gov.sa/e-invoicing/developer-portal/compliance';
$config['zatca_sandbox_reporting_api'] = 'https://gw-apic-gov.gazt.gov.sa/e-invoicing/developer-portal/invoices/reporting/single';
$config['zatca_sandbox_clearance_api'] = 'https://gw-apic-gov.gazt.gov.sa/e-invoicing/developer-portal/invoices/clearance/single';

/**
 * ZATCA Production Environment URLs
 */
$config['zatca_production_compliance_api'] = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/compliance';
$config['zatca_production_reporting_api'] = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/invoices/reporting/single';
$config['zatca_production_clearance_api'] = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/invoices/clearance/single';

/**
 * ZATCA Environment Setting
 * 
 * Controls which API endpoints to use (sandbox or production)
 * Set to true for production, false for sandbox/testing
 * 
 * @var bool
 */
$config['zatca_is_production'] = false; // Change to true for production environment

/**
 * ZATCA Portal URL
 */
$config['zatca_portal_url'] = 'https://fatoora.zatca.gov.sa/';

// ==============================================================
// ZATCA INVOICE SETTINGS
// ==============================================================

/**
 * Default Invoice Type Code
 * 0211 = B2C Simplified Invoice (Reporting)
 * 0100 = B2B Standard Invoice (Clearance)
 */
$config['zatca_default_invoice_type'] = '0211';

/**
 * Credit Note Type Code
 * 0200 = Credit Note (for refunds/returns)
 */
$config['zatca_credit_note_type'] = '0200';

/**
 * Default Country Code (Saudi Arabia)
 */
$config['zatca_country_code'] = 'SA';

/**
 * Default Currency Code
 */
$config['zatca_currency_code'] = 'SAR';

/**
 * Default VAT Rate (15%)
 */
$config['zatca_vat_rate'] = 15.00;

/**
 * Default Tax Scheme
 */
$config['zatca_tax_scheme'] = 'VAT';

// ==============================================================
// ZATCA XML NAMESPACES
// ==============================================================

/**
 * UBL 2.1 Namespaces
 */
$config['zatca_xmlns_invoice'] = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';
$config['zatca_xmlns_cac'] = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
$config['zatca_xmlns_cbc'] = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
$config['zatca_xmlns_ext'] = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';
$config['zatca_xmlns_sig'] = 'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2';
$config['zatca_xmlns_ds'] = 'http://www.w3.org/2000/09/xmldsig#';

// ==============================================================
// ZATCA API SETTINGS
// ==============================================================

/**
 * API Timeout (seconds)
 */
$config['zatca_api_timeout'] = 30;

/**
 * API Accept Version Header
 */
$config['zatca_api_version'] = 'V2';

/**
 * API Accept Language
 */
$config['zatca_api_language'] = 'en';

/**
 * SSL Verification (set to true in production)
 */
$config['zatca_ssl_verify_peer'] = false;
$config['zatca_ssl_verify_host'] = false;

// ==============================================================
// ZATCA SIGNATURE SETTINGS
// ==============================================================

/**
 * Hash Algorithm
 */
$config['zatca_hash_algorithm'] = 'sha256';

/**
 * Signature Algorithm
 */
$config['zatca_signature_algorithm'] = OPENSSL_ALGO_SHA256;

/**
 * RSA Key Size
 */
$config['zatca_rsa_key_size'] = 2048;

/**
 * Canonicalization Method
 */
$config['zatca_canonicalization_method'] = 'http://www.w3.org/2006/12/xml-c14n11';

/**
 * Signature Method
 */
$config['zatca_signature_method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';

/**
 * Digest Method
 */
$config['zatca_digest_method'] = 'http://www.w3.org/2001/04/xmlenc#sha256';

// ==============================================================
// ZATCA QR CODE SETTINGS (TLV Format)
// ==============================================================

/**
 * QR Code TLV Tags
 */
$config['zatca_qr_tag_seller_name'] = 1;
$config['zatca_qr_tag_vat_number'] = 2;
$config['zatca_qr_tag_timestamp'] = 3;
$config['zatca_qr_tag_invoice_total'] = 4;
$config['zatca_qr_tag_vat_amount'] = 5;

// ==============================================================
// ZATCA DEFAULT VALUES
// ==============================================================

/**
 * Default Previous Invoice Hash (for first invoice)
 */
$config['zatca_default_previous_hash'] = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';

/**
 * Default City Name
 */
$config['zatca_default_city'] = 'Riyadh';

// ==============================================================
// ZATCA LOGGING SETTINGS
// ==============================================================

/**
 * Enable ZATCA Logging
 */
$config['zatca_enable_logging'] = true;

/**
 * Log Level (info, error, debug)
 */
$config['zatca_log_level'] = 'info';

// ==============================================================
// ZATCA PERFORMANCE SETTINGS
// ==============================================================

/**
 * Enable Async Submission (future enhancement)
 */
$config['zatca_async_enabled'] = false;

/**
 * Cache ZATCA Configuration (seconds)
 */
$config['zatca_config_cache_ttl'] = 300; // 5 minutes
