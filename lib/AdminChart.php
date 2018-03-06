<?php
class AdminChart
{
    public static function factory($style)
    {
        return new AdminChartInstance();
    }
}

class AdminChartInstance
{
    private $options = array();

    public function __construct()
    {
        $options = array();
        $options['title']['text'] = '未设置';
//        $options['title']['left'] = 'center';
        $options['legend'] = array(
//            'left'=>'left',
            'data'=>array()
        );
        $options['tooltip'] = array(
            'trigger'=>'item',
            'formatter'=>'{a} <br/>{b} : {c}'
        );
        $options['xAxis'] = array(
            'type'=>'category',
            'name'=>'x',
            'splitLine'=>array('show'=>false),
            'data'=>array(),
        );
        $options['yAxis'] = array(
            'type'=>'log',
            'name'=>'y',
        );
        $this->options = $options;
    }

    public function setTitle($title)
    {
        $this->options['title']['text'] = $title;
    }

    public function setXAxis($xRange)
    {
        $this->options['xAxis']['data'] = $xRange;
    }

    public function addLine($name, $records)
    {
        $this->options['legend']['data'][] = $name;
        $y_params = array();
        foreach ($this->options['xAxis']['data'] as $key) {
            $y_params[] = isset($records[$key]) ? $records[$key] : 0;
        }
        $this->options['series'][] = array(
            'name'=>$name,
            'type'=>'line',
            'data'=> $y_params,
        );
    }

    public function getOptions()
    {
        return $this->options;
    }
}