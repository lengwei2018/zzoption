<?php
namespace calendar;

/**
 * Calendar类
 */
class Calendar
{
    /**
     * threshold
     * @author Lothar
     * @deprecated 生成日历的各个边界值
     * @deprecated 1)计算这个月总天数
     * @deprecated 2)计算这个月第一天与最后一天，各是星期几
     * @deprecated 3)计算日历中的第一个日期与最后一个日期
     * @param string $year
     * @param string $month
     * @return array
     */
    public function threshold($year, $month)
    {
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $lastDay  = strtotime('+1 month -1 day', $firstDay);
        //取得天数
        $days = date("t", $firstDay);
        //取得第一天是星期几
        $firstDayOfWeek = date('N', $firstDay);
        //获得最后一天是星期几
        $lastDayOfWeek = date('N', $lastDay);

        //上一个月最后一天
        $lastMonthDate      = strtotime('-1 day', $firstDay);
        $lastMonthOfLastDay = date('d', $lastMonthDate);
        //下一个月第一天
        $nextMonthDate       = strtotime('+1 day', $lastDay);
        $nextMonthOfFirstDay = strtotime('+1 day', $lastDay);

        //日历的第一个日期
        if ($firstDayOfWeek == 7) {
            $firstDate = $firstDay;
        } else {
            $firstDate = strtotime('-' . $firstDayOfWeek . ' day', $firstDay);
        }

        //日历的最后一个日期
        if ($lastDayOfWeek == 6) {
            $lastDate = $lastDay;
        } elseif ($lastDayOfWeek == 7) {
            $lastDate = strtotime('+6 day', $lastDay);
        } else {
            $lastDate = strtotime('+' . (6 - $lastDayOfWeek) . ' day', $lastDay);
        }

        return array(
            'days'               => $days,
            'firstDay'           => $firstDay,
            'lastDay'            => $lastDay,
            'firstDayOfWeek'     => $firstDayOfWeek,
            'lastDayOfWeek'      => $lastDayOfWeek,
            'lastMonthOfLastDay' => $lastMonthOfLastDay,
            'firstDate'          => $firstDate,
            'lastDate'           => $lastDate,
            'year'               => $year,
            'month'              => $month,
        );
    }

    /**
     * [caculate 计算日历的天数与样式]
     * @author Lothar
     * @deprecated
        1)将上个月的天数计算出来，本月第一天的星期不是星期天的话，就需要根据上个月的最后一天计算
        2)将本月的天数遍历出来，如果是休息天就加上特殊的css样式
        3)将下个月的天数计算出来，分三种情况，星期日、星期六和工作日
     * @param  [type] $calendar [通过threshold方法计算后的数据]
     * @return [type]           [description]
     */
    public function caculate($calendar, $cwk=[], $cws=[])
    {
        $days               = $calendar['days'];
        $firstDayOfWeek     = $calendar['firstDayOfWeek']; //本月第一天的星期
        $lastDayOfWeek      = $calendar['lastDayOfWeek']; //本月最后一天的星期
        $lastMonthOfLastDay = $calendar['lastMonthOfLastDay']; //上个月的最后一天
        $year               = $calendar['year'];
        $month              = $calendar['month'];

        $dates = array();

        //上月日历信息
        if ($firstDayOfWeek != 7) {
            $lastDays = array();
            $current  = $lastMonthOfLastDay; //上个月的最后一天
            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                array_push($lastDays, $current); //添加上一个月的日期天数
                $current--;
            }
            $lastDays = array_reverse($lastDays); //反序
            foreach ($lastDays as $index => $day) {
                array_push($dates, array('day' => '', 'tdclass' => '', 'pclass' => 'outter', 'type'=>'0', 'calenId'=>'0'));
            }
        }

        //本月日历信息
        for ($i = 1; $i <= $days; $i++) {
            $isTrade = true;$type='0';$calenId='0';
            $tdclass = '';

            $isRest = $this->_checkIsRest($year, $month, $i);
            if ($isRest) {
                $type = '1';
                $tdclass = 'rest';
            }
            if (isset($cwk[$i])) {
                $type = $cwk[$i];
                $isTrade = ($type>0)?false:true;
            }
            if (isset($cws[$i])) {
                $isTrade = ($cws[$i]['is_trade']==1)?true:false;
                $calenId = $cws[$i]['id'];
            }

            //判断是否是休息日、交易日
            array_push($dates, array(
                'day'       => $i, 
                'tdclass'   => $tdclass, 
                'pclass'    => ($isTrade ? '' : 'holiday'),
                'type'      => $type,
                'calenId'   => $calenId,
            ));
        }

        //下月日历信息
        if ($lastDayOfWeek == 7) {
            //最后一天是星期日
            $length = 6;
        } elseif ($lastDayOfWeek == 6) {
            //最后一天是星期六
            $length = 0;
        } else {
            $length = 6 - $lastDayOfWeek;
        }
        for ($i = 1; $i <= $length; $i++) {
            array_push($dates, array('day' => '', 'tdclass' => '', 'pclass' => 'outter', 'type'=>'0', 'calenId'=>'0'));
        }

        return $dates;
    }

    /**
     * 生成初始化日历 [cmf_stock_calendar]
     * @param  [type] $year  [年份]
     * @param  [type] $month [月份]
     * @param  [type] $cwk   [接口数据]
     * @return [type]        [description]
     */
    public function getWork($year, $month, $cwk)
    {
        //取得天数
        $days = date("t", mktime(0,0,0,$month,1,$year));

        $dates = array();
        //本月日历信息
        for ($i = 1; $i <= $days; $i++) {
            $isRest = $this->_checkIsRest($year, $month, $i);
            $type = empty($isRest) ? 0 : 1;

            $wk = ($month<10?'0'.$month:$month).($i<10?'0'.$i:$i);
            $type = isset($cwk[$wk]) ? $cwk[$wk] : $type;
            $isTrade = $type>0 ? 0 : 1;

            array_push($dates, array(
                'type'      => $type,
                'is_trade'  => $isTrade,
                'time'      => mktime(0,0,0,$month,$i,$year),
                // 'date'      => $year.'-'.$month.'-'.$i,
                'day'       => $i,
            ));
        }

        return $dates;
    }

    /**
     * draw
     * @author Lothar
     * @deprecated 画表格，设置table中的tr与td
     * @deprecated 1)数据将要用table标签来显示，所以这里要将各个tr下面的td排列好
     * @deprecated 2)$index % 7 == 0 计算表格每行的第一列
     * @deprecated 3)$index % 7 == 6 || $index == ($length-1) 计算每行的最后一列，或$caculate的最后一个数据
     * @deprecated 4)将中间行添加到$tr中，就是每一行的array
     * @param array $caculate 通过caculate方法计算后的数据
     */
    public function draws($caculate)
    {
        $tr = $result = array();

        // 不足则自动填充
        $week = date('w',$caculate[0]['time']);
        if ($week>0) {
            for ($sp=0; $sp <$week; $sp++) { 
                $fill[] = ['id'=>'','type'=>'','is_trade'=>'','time'=>'','day'=>''];
            }
            $caculate = array_merge($fill,$caculate);
        }
        $length = count($caculate);
       
        foreach ($caculate as $index => $date) {
            if ($index % 7 == 0 ) {
                //第一列
                $tr = array($date);
                
            } elseif ($index % 7 == 6 || $index == ($length - 1)) {
                array_push($tr, $date);
                array_push($result, $tr); //添加到返回的数据中
                $tr = array(); //清空数组列表
            } else {
                array_push($tr, $date); 
            } 
        }
        //最后添加，否则最后一天是周日会遗失
        if(!empty($tr)){
            array_push($result, $tr);
        } 
        return $result;
    }

    /**
     * @author Pwstrick
     * @deprecated 判断是否是休息天
     */
    private function _checkIsRest($year, $month, $day)
    {
        $date = mktime(0, 0, 0, $month, $day, $year);
        $week = date("N", $date);
        return $week == 7 || $week == 6;
    }
}