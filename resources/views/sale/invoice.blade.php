<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{url('public/logo', $general_setting->site_logo)}}" />
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <link rel="stylesheet" href="<?php echo asset('vendor/font-awesome/css/font-awesome.min.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('vendor/dripicons/webfont.css') ?>" type="text/css">

    <style type="text/css">
        * {
            font-size: 14px;
            line-height: 24px;
            font-family: 'Ubuntu', sans-serif;
            text-transform: capitalize;
        }
        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor:pointer;
        }

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        tr {border-bottom: 1px dotted #ddd;}
        td,th {padding: 7px 0;width: 50%;}

        table {width: 100%;}
        tfoot tr th:first-child {text-align: left;}

        .centered {
            text-align: center;
            align-content: center;
        }
        small{font-size:11px;}

        @media print {
            * {
                font-size:12px;
                line-height: 20px;
            }
            td,th {padding: 5px 0;}
            .hidden-print {
                display: none !important;
            }
            @page { margin: 1.5cm 0.5cm 0.5cm; }
            @page:first { margin-top: 0.5cm; }
            tbody::after {
                content: ''; display: block;
                page-break-after: always;
                page-break-inside: avoid;
                page-break-before: avoid;        
            }
        }
    </style>
  </head>
<body>

<div style="max-width:400px;margin:0 auto">
    @php $pos_url = url('pos'); @endphp
    <div class="hidden-print" style="margin-bottom: 12px;">
        <div style="background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 8px; padding: 10px 14px; margin-bottom: 10px; font-size: 13px; color: #0369a1; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="display: block; font-size: 13px;"><i class="fa fa-print"></i> Auto-Printing Receipt...</strong>
                <span style="font-size: 11px; color: #0284c7;" id="redirect-timer-msg">Returning to POS in <span id="countdown" style="font-weight: bold;">4</span>s</span>
            </div>
            <a href="{{$pos_url}}" class="btn btn-primary" style="margin: 0; padding: 5px 12px; width: auto; font-size: 12px; border-radius: 6px; background-color: #0284c7;"><i class="fa fa-arrow-left"></i> Return Now</a>
        </div>
        <table style="width: 100%;">
            <tr>
                <td style="padding-right: 4px; width: 50%;"><a href="{{$pos_url}}" class="btn btn-info" style="margin: 0; border-radius: 6px;"><i class="fa fa-arrow-left"></i> {{trans('file.Back')}}</a> </td>
                <td style="padding-left: 4px; width: 50%;"><button onclick="triggerPrint();" class="btn btn-primary" style="margin: 0; border-radius: 6px;"><i class="dripicons-print"></i> {{trans('file.Print')}}</button></td>
            </tr>
        </table>
        <br>
    </div>
        
    <div id="receipt-data">
        <div class="centered">
            @if($general_setting->site_logo)
                <img src="{{url('public/logo', $general_setting->site_logo)}}" height="42" width="50" style="margin:10px 0;filter: brightness(0);">
            @endif
            
            <h2>{{$lims_biller_data->company_name}}</h2>
            
            <p>{{trans('file.Address')}}: {{$lims_warehouse_data->address}}
                <br>{{trans('file.Phone Number')}}: {{$lims_warehouse_data->phone}}
            </p>
        </div>
        <p>{{trans('file.Date')}}: {{$lims_sale_data->created_at}}<br>
            {{trans('file.reference')}}: {{$lims_sale_data->reference_no}}<br>
            {{trans('file.customer')}}: {{$lims_customer_data->name}}
        </p>
        <table class="table-data">
            <tbody>
                <?php $total_product_tax = 0;?>
                @foreach($lims_product_sale_data as $key => $product_sale_data)
                <?php 
                    $lims_product_data = \App\Product::find($product_sale_data->product_id);
                    if($product_sale_data->variant_id) {
                        $variant_data = \App\Variant::find($product_sale_data->variant_id);
                        $product_name = $lims_product_data->name.' ['.$variant_data->name.']';
                    }
                    elseif($product_sale_data->product_batch_id) {
                        $product_batch_data = \App\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                        $product_name = $lims_product_data->name.' ['.trans("file.Batch No").':'.$product_batch_data->batch_no.']';
                    }
                    else
                        $product_name = $lims_product_data->name;
                ?>
                <tr>
                    <td colspan="2">
                        <strong>{{$product_name}}</strong>
                        <br><span style="font-family: 'Courier New', Courier, monospace; font-size: 11px; font-weight: bold; color: #1e293b;">[UPC / S/N: {{$lims_product_data->code}}]</span>
                        <br>{{$product_sale_data->qty}} x {{number_format((float)($product_sale_data->total / $product_sale_data->qty), 2, '.', '')}}

                        @if($product_sale_data->tax_rate)
                            <?php $total_product_tax += $product_sale_data->tax ?>
                            [{{trans('file.Tax')}} ({{$product_sale_data->tax_rate}}%): {{$product_sale_data->tax}}]
                        @endif
                    </td>
                    <td style="text-align:right;vertical-align:bottom">{{number_format((float)$product_sale_data->total, 2, '.', '')}}</td>
                </tr>
                @endforeach
            
            <!-- <tfoot> -->
                <tr>
                    <th colspan="2" style="text-align:left">{{trans('file.Total')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->total_price, 2, '.', '')}}</th>
                </tr>
                @if($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                <tr>
                    <td colspan="2">IGST</td>
                    <td style="text-align:right">{{number_format((float)$total_product_tax, 2, '.', '')}}</td>
                </tr>
                @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                <tr>
                    <td colspan="2">SGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), 2, '.', '')}}</td>
                </tr>
                <tr>
                    <td colspan="2">CGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), 2, '.', '')}}</td>
                </tr>
                @endif
                @if($lims_sale_data->order_tax)
                <tr>
                    <th colspan="2" style="text-align:left">{{trans('file.Order Tax')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->order_tax, 2, '.', '')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->order_discount)
                <tr>
                    <th colspan="2" style="text-align:left">{{trans('file.Order Discount')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->order_discount, 2, '.', '')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->coupon_discount)
                <tr>
                    <th colspan="2" style="text-align:left">{{trans('file.Coupon Discount')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->coupon_discount, 2, '.', '')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->shipping_cost)
                <tr>
                    <th colspan="2" style="text-align:left">{{trans('file.Shipping Cost')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->shipping_cost, 2, '.', '')}}</th>
                </tr>
                @endif
                <tr>
                    <th colspan="2" style="text-align:left">{{trans('file.grand total')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->grand_total, 2, '.', '')}}</th>
                </tr>
                <tr>
                    @if($general_setting->currency_position == 'prefix')
                    <th class="centered" colspan="3">{{trans('file.In Words')}}: <span>{{$currency->code}}</span> <span>{{str_replace("-"," ",$numberInWords)}}</span></th>
                    @else
                    <th class="centered" colspan="3">{{trans('file.In Words')}}: <span>{{str_replace("-"," ",$numberInWords)}}</span> <span>{{$currency->code}}</span></th>
                    @endif
                </tr>
            </tbody>
            <!-- </tfoot> -->
        </table>
        <table>
            <tbody>
                @foreach($lims_payment_data as $payment_data)
                <tr style="background-color:#ddd;">
                    <td style="padding: 5px;width:30%">{{trans('file.Paid By')}}: {{$payment_data->paying_method}}</td>
                    <td style="padding: 5px;width:40%">{{trans('file.Amount')}}: {{number_format((float)$payment_data->amount, 2, '.', '')}}</td>
                    <td style="padding: 5px;width:30%">{{trans('file.Change')}}: {{number_format((float)$payment_data->change, 2, '.', '')}}</td>
                </tr>
                @if($payment_data->payment_note)
                <tr style="background-color:#f1f5f9;">
                    <td colspan="3" style="padding: 4px 6px; font-size: 11px; color: #1e293b;"><strong>{{trans('file.Note')}} / Ref:</strong> {{$payment_data->payment_note}}</td>
                </tr>
                @endif
                @endforeach
                <tr><td class="centered" colspan="3">{{trans('file.Thank you for shopping with us. Please come again')}}</td></tr>
                <tr>
                    <td class="centered" colspan="3">
                    <?php echo '<img style="margin-top:10px;" src="data:image/png;base64,' . DNS1D::getBarcodePNG($lims_sale_data->reference_no, 'C128') . '" width="300" alt="barcode"   />';?>
                    <br>
                    <?php echo '<img style="margin-top:10px;" src="data:image/png;base64,' . DNS2D::getBarcodePNG($lims_sale_data->reference_no, 'QRCODE') . '" alt="barcode"   />';?>    
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- <div class="centered" style="margin:30px 0 50px">
            <small>{{trans('file.Invoice Generated By')}} {{$general_setting->site_title}}.
            {{trans('file.Developed By')}} <a href="https://aarambhx-tech.web.app/" target="_blank" style="text-decoration:none;color:#000;">TECH_BOY_LALITH</a></strong></small>
        </div> -->
    </div>
</div>

<script type="text/javascript">
    // Clear only POS active cart storage items, preserving user session
    var posKeys = [
        "tbody-id", "localStorageProductId", "localStorageProductCode",
        "localStorageSaleUnit", "localStorageTempUnitName", "localStorageSaleUnitOperator",
        "localStorageSaleUnitOperationValue", "localStorageTaxName", "localStorageTaxRate",
        "localStorageTaxMethod", "localStorageQty", "order-discount-value",
        "order-discount", "order-tax-rate-select", "shipping-cost-val"
    ];
    for (var i = 0; i < posKeys.length; i++) {
        try { localStorage.removeItem(posKeys[i]); } catch(e){}
    }

    var posReturnUrl = "{{$pos_url}}";
    var countdownSec = 4;
    var timerInterval = null;

    function returnToPos() {
        if (timerInterval) clearInterval(timerInterval);
        window.location.href = posReturnUrl;
    }

    function triggerPrint() {
        try {
            window.print();
        } catch (e) {
            console.error("Print trigger error:", e);
        }
    }

    // Automatically trigger print as soon as page and barcode images load
    window.addEventListener('load', function() {
        setTimeout(function() {
            triggerPrint();
        }, 300);

        timerInterval = setInterval(function() {
            countdownSec--;
            var countdownEl = document.getElementById('countdown');
            if (countdownEl) countdownEl.innerText = countdownSec;
            if (countdownSec <= 0) {
                returnToPos();
            }
        }, 1000);
    });

    // When print dialog is finished or closed, return immediately to POS
    window.onafterprint = function() {
        setTimeout(returnToPos, 300);
    };
</script>

</body>
</html>
