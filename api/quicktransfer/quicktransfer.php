<?php
// echo "<pre>"; print_r($_POST); die;
if (!empty($_POST)) {
    $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $payin_request_id = $_POST['transaction_id']; // Should be unique from Merchant Reference
    $payin_api_token = $_POST['merchant_code'] . '-' . $payin_request_id;
    $vstore_id = $_POST['merchant_code'];
    $customer_zip = $_POST['customer_zip'];
    $customer_addressline_1 = $_POST['customer_addressline_1'];
    $customer_city = $_POST['customer_city'];
    $customer_state = $_POST['customer_city'];
    $customer_phone = $_POST['customer_phone'];
    $price = $_POST['amount'];
    $Amount = (int)$price;
    $customer_email = $_POST['customer_email'];
    $customer_country = $_POST['customer_country']; // Customer Country
    $customer_name = $_POST['customer_name']; // Customer Name
    $product_name = 'test product'; // Any Thing
    

   

    $bankcode = "QTSE";
    $customer_bank_code = $_POST['currency'];

    $FrontURI = "https://payin.pay2rax.com/api/quicktransfer/response.php";
    $BackURI = "https://payin.pay2rax.com/api/quicktransfer/response.php";

    $curr = $_POST['currency'];
    if ($curr == 'CNY') {
        $merchant_account = $_POST['merchant_account_cny'];
        $merchantcontrol = $_POST['merchantcontrol_cny'];
    } elseif ($curr == 'USD') {
        $merchant_account = $_POST['merchant_account_usd'];
        $merchantcontrol = $_POST['merchantcontrol_usd'];
    } elseif ($curr == 'THB') {
        // $merchant_account = '902095';
        // $merchantcontrol = 'B90FC854CFBDE8BBA24392C309ECE710';
        $merchant_account = $_POST['merchant_account_thb'];
        $merchantcontrol = $_POST['merchantcontrol_thb'];
    }

    $payin_notify_url = $_POST['callback_url'];
    $payin_success_url = $_POST['callback_url']; // Success CallBack URL
    $payin_error_url = $_POST['callback_url'];
    if (!empty($_POST)) {
        //echo "<pre>"; print_r($_POST); die;
        date_default_timezone_set('Asia/Phnom_Penh');
        $created_date = date("Y-m-d H:i:s");
        include("../../connection.php");
        try {
            $query2 = "INSERT INTO `gtech_payins`( `client_ip`, `payin_api_token`, `vstore_id`, `action`, `source`, `source_url`, `source_type`, `price`, `curr`, `product_name`, `remarks`, 
        `customer_name`, `customer_email`, `customer_addressline_1`, `customer_addressline_2`, `customer_city`, `customer_state`, `customer_country`, `customer_zip`,
        `customer_phone`, `customer_bank_name`, `customer_bank_code`, `payin_request_id`, `payin_notify_url`, `payin_success_url`, `payin_error_url`, `orderstatus`, `created_at`)
        VALUES ( '$client_ip', '$payin_api_token', '$vstore_id', 'checkout', 'checkout-Encode', 'quickTransfer', '$source_type', '$Amount', '$curr', '$product_name', '$remarks',
        '$customer_name', '$customer_email', '$customer_addressline_1', '$customer_addressline_2', '$customer_city', '$customer_state', '$customer_country', '$customer_zip',
        '$customer_phone', '$bankcode', '$customer_bank_code', '$payin_request_id', '$payin_notify_url', '$payin_success_url', '$payin_error_url', 'pending', '$created_date')";
            $result = mysqli_query($link, $query2);
            if (!empty($result)) {
                // echo "Data inserted successfully!";
            } else {
                throw new Exception("Query execution failed: " . mysqli_error($link));
                die;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            die;
        }
    }
?>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript" language="JavaScript">
        var HmacSHA1 = HmacSHA1 || function(g, l) {
            var e = {},
                d = e.lib = {},
                m = function() {},
                k = d.Base = {
                    extend: function(a) {
                        m.prototype = this;
                        var c = new m;
                        a && c.mixIn(a);
                        c.hasOwnProperty("init") || (c.init = function() {
                            c.$super.init.apply(this, arguments)
                        });
                        c.init.prototype = c;
                        c.$super = this;
                        return c
                    },
                    create: function() {
                        var a = this.extend();
                        a.init.apply(a, arguments);
                        return a
                    },
                    init: function() {},
                    mixIn: function(a) {
                        for (var c in a) a.hasOwnProperty(c) && (this[c] = a[c]);
                        a.hasOwnProperty("toString") && (this.toString = a.toString)
                    },
                    clone: function() {
                        return this.init.prototype.extend(this)
                    }
                },
                p = d.WordArray = k.extend({
                    init: function(a, c) {
                        a = this.words = a || [];
                        this.sigBytes = c != l ? c : 4 * a.length
                    },
                    toString: function(a) {
                        return (a || n).stringify(this)
                    },
                    concat: function(a) {
                        var c = this.words,
                            q = a.words,
                            f = this.sigBytes;
                        a = a.sigBytes;
                        this.clamp();
                        if (f % 4)
                            for (var b = 0; b < a; b++) c[f + b >>> 2] |= (q[b >>> 2] >>> 24 - 8 * (b % 4) & 255) << 24 - 8 * ((f + b) % 4);
                        else if (65535 < q.length)
                            for (b = 0; b < a; b += 4) c[f + b >>> 2] = q[b >>> 2];
                        else c.push.apply(c, q);
                        this.sigBytes += a;
                        return this
                    },
                    clamp: function() {
                        var a = this.words,
                            c = this.sigBytes;
                        a[c >>> 2] &= 4294967295 <<
                            32 - 8 * (c % 4);
                        a.length = g.ceil(c / 4)
                    },
                    clone: function() {
                        var a = k.clone.call(this);
                        a.words = this.words.slice(0);
                        return a
                    },
                    random: function(a) {
                        for (var c = [], b = 0; b < a; b += 4) c.push(4294967296 * g.random() | 0);
                        return new p.init(c, a)
                    }
                }),
                b = e.enc = {},
                n = b.Hex = {
                    stringify: function(a) {
                        var c = a.words;
                        a = a.sigBytes;
                        for (var b = [], f = 0; f < a; f++) {
                            var d = c[f >>> 2] >>> 24 - 8 * (f % 4) & 255;
                            b.push((d >>> 4).toString(16));
                            b.push((d & 15).toString(16))
                        }
                        return b.join("")
                    },
                    parse: function(a) {
                        for (var c = a.length, b = [], f = 0; f < c; f += 2) b[f >>> 3] |= parseInt(a.substr(f,
                            2), 16) << 24 - 4 * (f % 8);
                        return new p.init(b, c / 2)
                    }
                },
                j = b.Latin1 = {
                    stringify: function(a) {
                        var c = a.words;
                        a = a.sigBytes;
                        for (var b = [], f = 0; f < a; f++) b.push(String.fromCharCode(c[f >>> 2] >>> 24 - 8 * (f % 4) & 255));
                        return b.join("")
                    },
                    parse: function(a) {
                        for (var c = a.length, b = [], f = 0; f < c; f++) b[f >>> 2] |= (a.charCodeAt(f) & 255) << 24 - 8 * (f % 4);
                        return new p.init(b, c)
                    }
                },
                h = b.Utf8 = {
                    stringify: function(a) {
                        try {
                            return decodeURIComponent(escape(j.stringify(a)))
                        } catch (c) {
                            throw Error("Malformed UTF-8 data");
                        }
                    },
                    parse: function(a) {
                        return j.parse(unescape(encodeURIComponent(a)))
                    }
                },
                r = d.BufferedBlockAlgorithm = k.extend({
                    reset: function() {
                        this._data = new p.init;
                        this._nDataBytes = 0
                    },
                    _append: function(a) {
                        "string" == typeof a && (a = h.parse(a));
                        this._data.concat(a);
                        this._nDataBytes += a.sigBytes
                    },
                    _process: function(a) {
                        var c = this._data,
                            b = c.words,
                            f = c.sigBytes,
                            d = this.blockSize,
                            e = f / (4 * d),
                            e = a ? g.ceil(e) : g.max((e | 0) - this._minBufferSize, 0);
                        a = e * d;
                        f = g.min(4 * a, f);
                        if (a) {
                            for (var k = 0; k < a; k += d) this._doProcessBlock(b, k);
                            k = b.splice(0, a);
                            c.sigBytes -= f
                        }
                        return new p.init(k, f)
                    },
                    clone: function() {
                        var a = k.clone.call(this);
                        a._data = this._data.clone();
                        return a
                    },
                    _minBufferSize: 0
                });
            d.Hasher = r.extend({
                cfg: k.extend(),
                init: function(a) {
                    this.cfg = this.cfg.extend(a);
                    this.reset()
                },
                reset: function() {
                    r.reset.call(this);
                    this._doReset()
                },
                update: function(a) {
                    this._append(a);
                    this._process();
                    return this
                },
                finalize: function(a) {
                    a && this._append(a);
                    return this._doFinalize()
                },
                blockSize: 16,
                _createHelper: function(a) {
                    return function(b, d) {
                        return (new a.init(d)).finalize(b)
                    }
                },
                _createHmacHelper: function(a) {
                    return function(b, d) {
                        return (new s.HMAC.init(a,
                            d)).finalize(b)
                    }
                }
            });
            var s = e.algo = {};
            return e
        }(Math);
        (function() {
            var g = HmacSHA1,
                l = g.lib,
                e = l.WordArray,
                d = l.Hasher,
                m = [],
                l = g.algo.SHA1 = d.extend({
                    _doReset: function() {
                        this._hash = new e.init([1732584193, 4023233417, 2562383102, 271733878, 3285377520])
                    },
                    _doProcessBlock: function(d, e) {
                        for (var b = this._hash.words, n = b[0], j = b[1], h = b[2], g = b[3], l = b[4], a = 0; 80 > a; a++) {
                            if (16 > a) m[a] = d[e + a] | 0;
                            else {
                                var c = m[a - 3] ^ m[a - 8] ^ m[a - 14] ^ m[a - 16];
                                m[a] = c << 1 | c >>> 31
                            }
                            c = (n << 5 | n >>> 27) + l + m[a];
                            c = 20 > a ? c + ((j & h | ~j & g) + 1518500249) : 40 > a ? c + ((j ^ h ^ g) + 1859775393) : 60 > a ? c + ((j & h | j & g | h & g) - 1894007588) : c + ((j ^ h ^
                                g) - 899497514);
                            l = g;
                            g = h;
                            h = j << 30 | j >>> 2;
                            j = n;
                            n = c
                        }
                        b[0] = b[0] + n | 0;
                        b[1] = b[1] + j | 0;
                        b[2] = b[2] + h | 0;
                        b[3] = b[3] + g | 0;
                        b[4] = b[4] + l | 0
                    },
                    _doFinalize: function() {
                        var d = this._data,
                            e = d.words,
                            b = 8 * this._nDataBytes,
                            g = 8 * d.sigBytes;
                        e[g >>> 5] |= 128 << 24 - g % 32;
                        e[(g + 64 >>> 9 << 4) + 14] = Math.floor(b / 4294967296);
                        e[(g + 64 >>> 9 << 4) + 15] = b;
                        d.sigBytes = 4 * e.length;
                        this._process();
                        return this._hash
                    },
                    clone: function() {
                        var e = d.clone.call(this);
                        e._hash = this._hash.clone();
                        return e
                    }
                });
            g.SHA1 = d._createHelper(l);
            g.encrypt = d._createHmacHelper(l)
        })();
        (function() {
            var g = HmacSHA1,
                l = g.enc.Utf8;
            g.algo.HMAC = g.lib.Base.extend({
                init: function(e, d) {
                    e = this._hasher = new e.init;
                    "string" == typeof d && (d = l.parse(d));
                    var g = e.blockSize,
                        k = 4 * g;
                    d.sigBytes > k && (d = e.finalize(d));
                    d.clamp();
                    for (var p = this._oKey = d.clone(), b = this._iKey = d.clone(), n = p.words, j = b.words, h = 0; h < g; h++) n[h] ^= 1549556828, j[h] ^= 909522486;
                    p.sigBytes = b.sigBytes = k;
                    this.reset()
                },
                reset: function() {
                    var e = this._hasher;
                    e.reset();
                    e.update(this._iKey)
                },
                update: function(e) {
                    this._hasher.update(e);
                    return this
                },
                finalize: function(e) {
                    var d =
                        this._hasher;
                    e = d.finalize(e);
                    d.reset();
                    return d.finalize(this._oKey.clone().concat(e))
                }
            })
        })();

        function generatecontrol(pform) {
            var morder = parseInt(new Date().valueOf() / 1000); //generate a unique number
            pform.merchant_order.value = morder;
            var s = HmacSHA1.encrypt(pform.merchant_account.value +
                pform.amount.value +
                pform.currency.value +
                pform.first_name.value +
                pform.last_name.value +
                pform.address1.value +
                pform.city.value +
                pform.zip_code.value +
                pform.country.value +
                pform.phone.value +
                pform.email.value +
                pform.merchant_order.value +
                pform.merchant_product_desc.value +
                pform.return_url.value,
                pform.merchantcontrol.value);
            //alert('hash : ' + s);
            console.log(s);
            pform.control.value = s;
            pform.submit();
        }
    </script>
    <form action="https://payment.quicktransfer.asia/ChinaDebitCard" method="POST" name="testform">
        <!-- Version -->
        <input id="version" name="version" type="hidden" placeholder="" value="11">
        <!-- API Version -->
        <input id="apiversion" name="apiversion" type="hidden" placeholder="" value="3">
        <!-- CHINA Merchant Account  -->
        <input id="merchant_account" name="merchant_account" type="hidden" placeholder="CHINA Merchant Code" value="<?php echo $merchant_account; ?>">
        <!-- CHINA Merchant Partner Control -->
        <input id="merchantcontrol" name="merchantcontrol" type="hidden" placeholder="CHINA merchant partner control" value="<?php echo $merchantcontrol; ?>">
        <!-- Merchant Order -->
        <input id="merchant_order" name="merchant_order" type="hidden" placeholder="235423874" value="<?php echo $payin_request_id; ?>">
        <!-- Product Description -->
        <input id="merchant_product_desc" name="merchant_product_desc" type="hidden" placeholder="your procutname or description" value="<?php echo $product_name; ?>">
        <!-- First Name -->
        <input id="first_name" name="first_name" type="hidden" placeholder="John" value="<?php echo $customer_name; ?>">
        <!-- Last Name -->
        <input id="last_name" name="last_name" type="hidden" placeholder="test@test.com" value="<?php echo $payin_request_id; ?>">
        <!-- Country -->
        <input id="country" name="country" type="hidden" placeholder="ISO code (IN|TH|MY|ID|ZH)" value="<?php echo $customer_country; ?>">
        <!-- Email -->
        <input id="email" name="email" type="hidden" placeholder="test@test.com" value="<?php echo $customer_email; ?>">
        <!-- For min deposit 1000, it should be 100000 non floating value -->
        <input id="amount" name="amount" type="hidden" placeholder="1000" value="<?php echo $Amount; ?>00">
        <!-- For deposit 1000, it should be 100000. -->
        <!-- Currency -->
        <input id="currency" name="currency" type="hidden" placeholder="THB|INR|USD|CNY|MYR|IDR" value="<?php echo $curr; ?>">
        <!-- Address -->
        <input id="address1" name="address1" type="hidden" placeholder="" value="<?php echo $customer_addressline_1; ?>">
        <!-- Zip -->
        <input id="zip_code" name="zip_code" type="hidden" placeholder="" value="<?php echo $customer_zip; ?>">
        <!-- City -->
        <input id="city" name="city" type="hidden" placeholder="" value="<?php echo $customer_city; ?>">
        <!-- Phone -->
        <input id="phone" name="phone" type="hidden" placeholder="" value="<?php echo $customer_phone; ?>">
        <!-- Bank Code -->
        <input id="bankcode" name="bankcode" type="hidden" placeholder="" value="<?php echo $bankcode; ?>">
        <!-- Return URL -->
        <input id="return_url" name="return_url" type="hidden" placeholder="" value="<?php echo $FrontURI ?>">
        <!-- Server Return URL -->
        <input id="server_return_url" name="server_return_url" type="hidden" placeholder="" value="<?php echo $BackURI ?>">
        <!-- IP Address -->
        <input id="ipaddress" name="ipaddress" type="hidden" placeholder="" value="<?php echo $client_ip; ?>">
        <!-- Control (Generated by SHA1) -->
        <input id="control" name="control" type="hidden" placeholder="" value="" readonly>
        <!-- Button -->
        <button id="cartCheckout" style="display:none;" name="" class="btn btn-primary" OnClick="generatecontrol(this.form);">Submit</button>
    </form>
    <script type="text/javascript">
        jQuery(function() {
            jQuery('#cartCheckout').click();
        });
    </script>
<?php } else {
    echo "No Data Available or Invalid Request";
} ?>