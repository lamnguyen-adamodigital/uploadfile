<?php

namespace App\Libraries;

use DateTime;
use Carbon\Carbon;
use Akaunting\Money\Money;
use App\Models\Users;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use File;
use Image;


class Ultilities
{
 /**
     * call api
     * @author lamnt
     * @param object host
     * @param object getParam
     * @param object $param
     * date 2020 04 21
     */
    public static function callAPI($endpoint, $bodyRequest="", $header="" ,$code = 0)
    {
        $client = new Client();
        $host = ENDPOINT_PRODUCTION_POS;
        if($code == 0){
            $body = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:TPAPIPosIntfU-ITPAPIPOS">
            <soapenv:Header/>
            <soapenv:Body>
               <urn:'.$endpoint.' soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                  <Request xsi:type="urn:T'.$endpoint.'Request" xmlns:urn="urn:TPAPIPosIntfU">
                     <Password xsi:type="xsd:string">'.PASSWORD_PRODUCTION_API.'</Password>
                     <UserName xsi:type="xsd:string">'.USERNAME_PRODUCTION_API.'</UserName>
                     '.$bodyRequest.'
                  </Request>
               </urn:'.$endpoint.'>
            </soapenv:Body>
            </soapenv:Envelope>';
        }else{
            $body = $header.'<soapenv:Header/>
            <soapenv:Body>
               <urn:'.$endpoint.' soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                  <Request xsi:type="urn:T'.$endpoint.'Request" xmlns:urn="urn:TPAPIPosIntfU">
                     <Password xsi:type="xsd:string">@ndy95872</Password>
                     <UserName xsi:type="xsd:string">Andy</UserName>
                     '.$bodyRequest.'
                  </Request>
               </urn:'.$endpoint.'>
            </soapenv:Body>
         </soapenv:Envelope>';
        }

        $header = [
            'Content-Type' => 'text/xml; charset=UTF8'
        ];

        try {
            $response = $client->post($host, [
                'headers' => $header,
                'body' =>  $body
            ]);
        } catch (\Exception $ex) {
            $ex->getMessage();
            return null;
        }
        $results = null;
        if (
            $response->getStatusCode() == Response::HTTP_OK || $response->getStatusCode() == Response::HTTP_CREATED ||
            $response->getStatusCode() == Response::HTTP_ACCEPTED || $response->getStatusCode() == Response::HTTP_NO_CONTENT
        ) {
            $results =$response->getBody()->getContents();
            $fileContents = str_replace(array("\n", "\r", "\t"), '', $results);
            $fileContents = trim(str_replace('"', "'", $fileContents));
            $xml = simplexml_load_string($fileContents);
            $xml->registerXPathNamespace('NS2', 'urn:TPAPIPosIntfU');
            $xml->registerXPathNamespace('NS3', 'urn:TPAPIPosTypesU');
            foreach ($xml->xpath('//return') as $item) {
                $json = json_encode($item);
                $convrt_arr = json_decode($json, true);
                break;
            }
        }
        if($endpoint == 'CreateOrder'){
            return $convrt_arr;
        }
        return $convrt_arr;
    }

    public static function uploadFile($file)
    {
        $publicPath = public_path('uploads');
        if (!File::exists($publicPath)) {
            File::makeDirectory($publicPath, 0775, true, true);
        }
        $name = time().'-'.$file->getClientOriginalName();
        $name = preg_replace('/\s+/', '', $name);
        $file->move(public_path('uploads'), $name);
        return $name;
    }

    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    public static function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d')
    {
        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }

    public static function listDayOfMonth($month = 0)
    {
        $query = 'this month';
        if ($month != 0) {
            $query = "$month month";
        }
        $firstDay = (new DateTime("first day of $query"))->format('Y-m-d');
        $lastDay = (new DateTime("last day of $query"))->format('Y-m-d');

        return self::dateRange($firstDay, $lastDay);
    }

    // public static function printPrice($value, $symbol = PRICE_PREFIX)
    // {
    //     if (is_string($value)) {
    //         $value = self::clearPrice($value);
    //     }
    //     $symbol_thousand = '.';
    //     $decimal_place = 0;
    //     $price = number_format($value, $decimal_place, '', $symbol_thousand);

    //     return $price.' '.$symbol;
    // }

    public static function dates_month($date, $format = 'Y-m-d')
    {
        $d = date_parse_from_format('m-Y', $date);

        $num = cal_days_in_month(CAL_GREGORIAN, $d['month'], $d['year']);
        $dates_month = array();

        for ($i = 1; $i <= $num; ++$i) {
            $mktime = mktime(0, 0, 0, $d['month'], $i, $d['year']);
            $date = date($format, $mktime);
            $dates_month[$i] = $date;
        }

        return $dates_month;
    }

    public static function clearXSS($string)
    {
        $string = nl2br($string);
        $string = trim(strip_tags($string));
        $string = self::removeScripts($string);

        return $string;
    }

    public static function removeScripts($str)
    {
        $regex =
            '/(<link[^>]+rel="[^"]*stylesheet"[^>]*>)|'.
            '<script[^>]*>.*?<\/script>|'.
            '<style[^>]*>.*?<\/style>|'.
            '<!--.*?-->/is';

        return preg_replace($regex, '', $str);
    }

    public static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }

    public static function is_serialized($data)
    {
        // if it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }

        return false;
    }

    public static function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) & !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) & !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED']) & !empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) & !empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED']) & !empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) & !empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    public static function clearPrice($price)
    {
        $price = str_replace(['.', ',', 'VNĐ'], '', $price);

        return (float) trim($price);
    }

    public static function Thumbnail($url = '', $with = 300, $height = 300, $t = 1)
    {
        if (empty($url)) {
            $url = 'img/no-image.jpg';
        }
        $url = urldecode($url);

        return  url($url);
    }

    public static function viewPhoneEmail($text)
    {
        $text = str_replace([',', ' - '], '<br>', $text);

        return $text;
    }

    public static function truncateWords($input, $numwords, $padding = '...')
    {
        $input = strip_tags($input);
        $output = strtok($input, " \n");
        while (--$numwords > 0) {
            $output .= ' '.strtok(" \n");
        }
        if ($output != $input) {
            $output .= $padding;
        }

        return $output;
    }

    //full url image, avatar...
    public static function replaceUrlImage($val)
    {
        $image = '';
        if (!empty($val)) {
            if (!filter_var($val, FILTER_VALIDATE_URL)) {
                $image = url('/uploads/'.$val);
            } else {
                $image = $val;
            }
        }

        return $image;

        // return URL_API.'/uploads/'.$val;
    }

    public static function sksort(&$array, $subkey = 'id', $sort_ascending = false)
    {
        if (count($array)) {
            $temp_array[key($array)] = array_shift($array);
        }

        foreach ($array as $key => $val) {
            $offset = 0;
            $found = false;
            foreach ($temp_array as $tmp_key => $tmp_val) {
                if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                    $temp_array = array_merge(
                        (array) array_slice($temp_array, 0, $offset),
                        array($key => $val),
                        array_slice($temp_array, $offset)
                    );
                    $found = true;
                }
                ++$offset;
            }
            if (!$found) {
                $temp_array = array_merge($temp_array, array($key => $val));
            }
        }

        if ($sort_ascending) {
            $array = array_reverse($temp_array);
        } else {
            $array = $temp_array;
        }
    }

    public static function merge_by_key(array $arr1, array $arr2, $key_replace)
    {
        $result = [];

        foreach ($arr1 as $value) {
            $key = array_search($value[$key_replace], array_column($arr2, $key_replace));

            if ($key !== false) {
                $result[] = array_merge($value, $arr2[$key]);
                unset($arr2[$key]);
            } else {
                $result[] = $value;
            }
        }
        $result = array_merge($result, $arr2);

        return $result;
    }

    public static function statusBooking($status)
    {
        $text = '';
        // 'pending' => 1,
        // 'going' => 2,
        // 'completed' => 3,
        // 'no_show' => 4,
        // 'cancel' => 5,

        switch ($status) {
            case 1:
                $text = __('api.booking_pending');
                break;
            case 2:
                $text = __('api.booking_on_going');
                break;
            case 3:
                $text = __('api.booking_completed');
                break;
            case 4:
                $text = __('api.booking_no_show');
                break;
            case 5:
            default:
                $text = __('api.booking_cancel');
                break;
        }

        return $text;
    }

    // public static function arrayDateFromNow($numberDay = DEFAULT_BREAK_DAYS, $date = null)
    // {
    //     $start = (!empty($date)) ? $date : Carbon::now()->startOfDay();

    //     for ($i = 0; $i < $numberDay; ++$i) {
    //         $dates[] = $start->copy()->addDays($i)->startOfDay()->format(DEFAULT_FORMAT_DATE);
    //     }

    //     return $dates;
    // }

    // public static function get_hours_range($start = 0, $end = 60 * 60 * 60, $step = DEFAULT_BREAK_TIME, $format = 'g:i a')
    // {
    //     $times = array();
    //     $start = $start + DEFAULT_HOUR_TIMEZONE;
    //     $end = $end + DEFAULT_HOUR_TIMEZONE;
    //     foreach (range($start, $end, $step) as $timestamp) {
    //         $hour_mins = gmdate('H:i', $timestamp);
    //         if (!empty($format)) {
    //             $times[$hour_mins] = gmdate($format, $timestamp);
    //         } else {
    //             $times[$timestamp] = $timestamp;
    //         }
    //     }
    //     $times = self::sortTime($times);

    //     return $times;
    // }

    // public static function priceFormat($price)
    // {
    //     $price = number_format($price, 2);
    //     $price = Money::USD($price, true)->getAmount();

    //     return $price;
    // }

    public static function viewPrice($price)
    {
        $price = $price / 100;

        return number_format($price, 2);
    }

    public static function sortTime($dataTimes)
    {
        $times = [];
        $now = Carbon::now()->format('Y-m-d');
        $tmpTime = [];
        foreach ($dataTimes as $key => $value) {
            $time = Carbon::parse($now.' '.$key)->timestamp;
            $tmpTime[$time] = $value;
        }
        ksort($tmpTime);

        $dataTimes = [];
        foreach ($tmpTime as $key => $value) {
            $time = date('H:i', $key);
            $dataTimes[$time] = $value;
        }

        return $dataTimes;
    }

    public static function mergeArraysValues($arrays)
    {
        // return array_merge(...$arrays);
        $data = [];
        foreach ($arrays as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $data[$key2] = $value2;
            }
        }
        $data = self::sortTime($data);

        return $data;
    }

    public static function arrayDiffByValueArray($array1, $array2 = null)
    {
        if (empty($array2)) {
            return $array1;
        }
        $data = [];

        foreach ($array1 as $key => $value) {
            if (!empty($array2[$key])) {
                $tmp = array_diff_assoc(array_keys($value), $array2[$key]);
                $data[$key] = $tmp;
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /***
     *
     * Y the amount to be compensated for the Merchant
     * X is the amount Lemon needs to pay to make up
     *   Y = X - (X * 0.34% + 0.5)
     *   Y = 0.966 * X - 0.5
     *   => X = (Y + 0.5) /0.966
     *
     */
    public static function calcPriceMissing($price)
    {
        $x = ((float) $price + 0.5) / 0.966;

        return $x;
    }

    public static function check_diff_multi($array1, $array2)
    {
        $result = array();
        foreach ($array1 as $key => $val) {
            if (isset($array2[$key])) {
                if (is_array($val) && $array2[$key]) {
                    $result[$key] = self::check_diff_multi($val, $array2[$key]);
                }
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }

    public static function getArrayByCount($array, $count)
    {
        $data = [];
        $i = 0;
        foreach ($array as $key => $value) {
            if ($i < $count) {
                $data[$key] = $value;
            }
            ++$i;
        }

        return $data;
    }

    public static function mergeValues($array)
    {
        $data = [];
        foreach ($array as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                foreach ($value2 as $key3 => $value3) {
                    $data[$key1][$key3] = $value3;
                }
            }
        }

        return $data;
    }

    public static function arrays_are_equal($array1, $array2)
    {
        array_multisort($array1);
        array_multisort($array2);

        return  serialize($array1) === serialize($array2);
    }

    public static function phoneStartsWith($str, $prefix, $pos = 0, $encoding = null)
    {
        if (is_null($encoding)) {
            $encoding = mb_internal_encoding();
        }
        return mb_substr($str, $pos, mb_strlen($prefix, $encoding), $encoding) === $prefix;
    }

    public static function replacePhone($phone)
    {
        if (!self::phoneStartsWith($phone, '+84') && !self::phoneStartsWith($phone, '0')) {
            $phone = '0'.$phone;
        }
        if (empty($phone)) {
            return null;
        }

        $search = array('(84)', '(+84)', '+84', ' ', '-');
        $replace = array('0', '0', '0', '', '');

        $phone = str_replace($search, $replace, Ultilities::clearXSS($phone));
        $phone = ltrim($phone,"0");
        $phone = '+84'.$phone;

        return $phone;
    }
}
