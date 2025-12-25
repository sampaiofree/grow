<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoppusProdutor extends Model
{
    use HasFactory;

    // Define a tabela associada a este modelo
    protected $table = 'doppus_produtor';

    // Campos permitidos para preenchimento em massa
    protected $fillable = [
        // Customer fields
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_doc_type',
        'customer_doc',
        'customer_ip_address',

        // Address fields
        'address_zipcode',
        'address_address',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',

        // Items fields
        'items_code',
        'items_name',
        'items_offer',
        'items_offer_name',
        'items_type',
        'items_value',

        // Affiliate fields
        'affiliate_code',
        'affiliate_name',
        'affiliate_email',

        // Recurrence fields
        'recurrence_code',
        'recurrence_periodicy',

        // Transaction fields
        'transaction_code',
        'transaction_registration_date',
        'transaction_items',
        'transaction_discount',
        'transaction_shipping',
        'transaction_subtotal',
        'transaction_interest',
        'transaction_interest_add',
        'transaction_total',
        'transaction_fee_transaction',
        'transaction_fee_doppus',
        'transaction_fee_affiliate',
        'transaction_fee_manager',
        'transaction_fee_coproducers',
        'transaction_fee_producer',

        // Payment fields
        'payment_method',
        'payment_plots',
        'payment_creditcard',
        'payment_brand',
        'payment_owner',
        'payment_due_date',
        'payment_digitable_line',
        'payment_brcode',

        // Shipping fields
        'shipping_method',
        'shipping_deadline',

        // Links fields
        'links_billet',
        'links_qrcode',
        'links_reprocess',
        'links_checkout',

        // Tracking fields
        'tracking_utm_source',
        'tracking_utm_medium',
        'tracking_utm_campaign',
        'tracking_utm_term',
        'tracking_utm_content',
        'tracking_src',

        // Status fields
        'status_registration_date',
        'status_code',
        'status_message',

        // Status log fields
        'status_log_registration_date',
        'status_log_code',
        'status_log_message',
    ];
}
