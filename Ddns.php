<?php
/**
 * DDNS based on dnspod.cn
 *
 * Created at 2018-02-08
 * by jay4497
 */

class Ddns
{
    protected $base_url = 'https://dnsapi.cn';

    private $domain;

    private $sub_domain;

    private $record_info;

    private $domain_info;

    private $login_token;

    private $format;

    private $lang;

    private $my_ip;

    public function __construct($login_token, $domain, $sub_domain = '', $lang = 'cn', $format = 'json')
    {
        $this->login_token = $login_token;
        $this->domain = $domain;
        $this->sub_domain = $sub_domain;
        $this->format = $format;
        $this->lang = $lang;

        $this->log('DDNS begin.');
    }

    public function get_record()
    {
        $url = '/Record.List';
        $params = [
            'domain' => $this->domain,
            'sub_domain' => $this->sub_domain ?: '',
            'offset' => 0,
            'length' => 1,
            'keyword' => ''
        ];
        $result = $this->request($url, $params, 'post');
        if (!empty($result)) {
            if ($result->status->code == 1) {
                $this->domain_info = $result->domain;
                $this->record_info = $result->records[0];
                return true;
            } else {
                $this->log($result->status->message);
                exit;
            }
        } else {
            $this->log('Request error.');
            exit;
        }
        return false;
    }

    public function set_record()
    {
        $url = '/Record.Modify';
        if ($this->check_ip()) {
            $params = [
                'domain' => $this->domain,
                'record_id' => $this->record_info->id,
                'sub_domain' => $this->sub_domain ?: '@',
                'record_type' => 'A',
                'record_line' => '默认',
                'value' => $this->my_ip,
                'ttl' => '10'
            ];

            $result = $this->request($url, $params, 'post');
            if (!empty($result)) {
                if ($result->status->code == 1) {
                    $success_message = 'The value of record changes to ' . $this->my_ip . ' from ' . $this->record_info->value;
                    $this->log($success_message);
                    $this->log($result->status->message);
                } else {
                    $this->log($result->status->message);
                }
            } else {
                $this->log('Request error.');
            }
        }

        $this->log('DDNS complete.');
    }

    public function check_ip()
    {
        $cur_ip = $this->get_ip();
        if($this->get_record()) {
            $record_ip = $this->record_info->value ?: $cur_ip;
            if ($cur_ip != $record_ip) {
                return true;
            }
        }
        return false;
    }

    private function build_params($data = [])
    {
        $public_params = [
            'login_token' => $this->login_token,
            'format' => $this->format,
            'lang' => $this->lang,
            'error_on_empty' => 'no'
        ];
        if (!empty($data)) {
            return array_merge($public_params, $data);
        }
        return $public_params;
    }

    private function get_ip()
    {
        $url = 'http://ip-api.com/json';
        $result = file_get_contents($url);
        $result = json_decode($result);
        if ($result->status == 'success') {
            $this->my_ip = $result->query;
        }
        return $this->my_ip;
    }

    private function request($url, $data, $method = 'post')
    {
        $_data = $this->build_params($data);
        $send_data = http_build_query($_data);
        $url = $this->base_url. $url;
        $res = curl_init();
        if (strtolower($method) == 'get') {
            $url = $url . '?' . $send_data;
            curl_setopt($res, CURLOPT_URL, $url);
        } else {
            curl_setopt($res, CURLOPT_URL, $url);
            curl_setopt($res, CURLOPT_POST, 1);
            curl_setopt($res, CURLOPT_POSTFIELDS, $send_data);
        }
        curl_setopt($res, CURLOPT_USERAGENT, 'Jay4497 DDNS Client/1.0.0 (jay4497@126.com)');
        curl_setopt($res, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($res, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($res);
        curl_close($res);
        return json_decode($result);
    }

    private function log($message)
    {
        $log_dir = './logs/ddns_php.log';
        if (is_file($log_dir)) {
            if (filesize($log_dir) > 5120000) {
                @unlink($log_dir);
            }
        }
        $pre_content = date('Y-m-d H:i:s', time()) . ' - ';
        $content = $pre_content . $message . PHP_EOL;
        file_put_contents($log_dir, $content, FILE_APPEND);
    }
}
