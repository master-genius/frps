<?php
namespace master\action;

use \errcode\errinfo;


class monitor {

    private $mem_file = '/proc/meminfo';

    public function sysload($req, $res) {
        $mem_total = 0;
        $mem_avali = 0;
        $fd = fopen($this->mem_file, "r");
        if (false === $fd) {
            return api_ret($res, [
                'mem'   => $mem_total,
                'ava_mem' => $mem_avali
            ]);
        }

        $str_total = fgets($fd, 512);
        $str_buffer = fgets($fd, 512);
        $str_avali = fgets($fd, 512);
        fclose($fd);

        $total = explode(':' , $str_total);
        $avali = explode(':', $str_avali);
        $used = (float)$total[1] - (float)$avali[1];
        $mem_ratio = $used / ((float)$total[1]);

        /*
            计算CPU使用率，从/proc/stat文件读取第一行信息，第四个时间片是
            从启动到现在的空闲时间：IDLE，(所有时间相加-IDLE)/总时间就是使用率
        */
        $fd = fopen('/proc/stat', "r");
        $cpu = 0;

        if ($fd !== false) {
            $cpu_tm_list = fgets($fd, 512);
            fclose($fd);
            $cpu_split = explode(' ', $cpu_tm_list);
            $tm_split = array_filter($cpu_split, function($a){
                if (is_numeric($a)) {
                    return true;
                }
            });
         
            $total_cpu_time = array_sum($tm_split);
            $idle = 0;
            $i = 0;
            foreach($tm_split as $t) {
                $i++;
                if ($i == 4) {
                    $idle = $t;
                    break;
                }
            }

            $cpu = ($total_cpu_time - $idle)/$total_cpu_time;
        }
        
        return api_ret($res, [
            'mem'     => trim($total[1], ' '),
            'ava_mem' => trim($avali[1], ' '),
            'mem_ratio'=> $mem_ratio,
            'cpu'   => $cpu
        ]);

    }

}

