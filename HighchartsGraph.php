<?php

/**
 * Class for charts support via Highcharts
 * (Charting library written in pure JavaScript, offering an easy way of adding interactive charts to your web site or web application.
 * http://www.highcharts.com/)
 */
class HighchartsGraph
{
	/**
	 * Array for transformation to JSON Highcharts options ( http://www.highcharts.com/docs/chart-concepts/understanding-highcharts )
	 * @var array
	 */
	private $options = [];

	/**
	 * Data array like ['2015-05-01' => ['ChartName1' => 111, 'ChartName2' => 222, 'ChartName3' => 333, ], '2015-05-02' => ['ChartName1' => 1111, 'ChartName2' => 2222, 'ChartName3' => 3333, ], ...]
	 * @var array
	 */
	public $data = [];

	/**
	 * Used Highcharts version
	 * @var string
	 */
	protected static $ver = '4.1.6';

	/**
	 * Expressions are used to remove quotes in the result output options
	 * Example: $this->expr("function() {return '' + this.x +': '+ this.y + ' ' + '°C'}"
	 * @var array
	 */
	protected $expr = [];

		/**
	 * @param $title
	 * @param $subtitle
	 * @param $kind
	 */
	function __construct($title, $subtitle = false, $kind = 'default')
	{
		$this->options['title']['text'] = $title;
		if ($subtitle)
		{
			$this->options['subtitle']['text'] = $subtitle;
		}

		$this->setOptions([
			'credits' => [
				'enabled' => false,
			],
		]);

		switch ($kind)
		{
			case 'fastline':
				$opt = [
					'chart' => [
						'type' => 'line',
						'zoomType' => 'xy',
					],
					'plotOptions' => [
						'series' => [
							'animation' => false,
							'enableMouseTracking' => false,
							'marker' => [
								'enabled' => false,
							],
							'dataGrouping' => [
								'enabled' => false,
							],
						],
					],
				];
				break;

			case 'default':
			case 'spline':
			default:
				$opt = [
					'chart' => [
						'type' => 'spline',
						'zoomType' => 'xy',
					],
					'plotOptions' => [
						'series' => [
							'states' => [
								'hover' => [
									'lineWidthPlus' => 3,
								],
							],
							'marker' => [
								'enabled' => false,
							],
						],
					],
					'tooltip' => [
						'shared' => true,
					],
					'xAxis' => [[
						'crosshair' => true,
					]],
				];
				break;
		}
		$this->setOptions($opt);
	}

	/**
	 * Apply options to current chart
	 * @param $arr
	 */
	function setOptions($arr)
	{
		$this->options = array_replace_recursive($this->options, $arr);
	}

	/**
	 * For deleting quotes in rendered options
	 * @param $s
	 * @return mixed
	 */
	function expr($s)
	{
		array_push($this->expr, $s);
		return $s;
	}

	/**
	 * Add point(s) of chart(s) for X Axis value $x
	 * $arr is array like ['ChartName1' => 111, 'ChartName2' => 222, 'ChartName3' => 333, ...]
	 * @param $x
	 * @param $arr
	 */
	function addPoint($x, $arr)
	{
			$this->data[$x] = $arr;
	}

	/**
	 * Sorting data by X-axis
	 * @param string $dir
	 */
	function sortData($dir = 'ASC')
	{
		if ($dir == 'DESC')
		{
			krsort($this->data);
		}
		else
		{
			ksort($this->data);
		}
	}

	/**
	 * Delete empty charts from $data
	 */
	protected function deleteEmptyData()
	{
		$filled = [];
		foreach($this->data as $key => $val_arr)
		{
			if (empty($filled))
			{
				foreach ($val_arr as $k => &$v)
				{
					$filled[$k] = $v;
				}
			}

			foreach ($val_arr as $k => &$v)
			{
				$filled[$k] = $filled[$k] || $v;
			}
		}

		foreach ($filled as $k => &$v)
		{
			if (!$v)
			{
				foreach($this->data as $key => &$val_arr)
				{
					unset($val_arr[$k]);
				}
			}
		}
	}

	/**
	 * Render setted $this->options with $this->data to JSON for chart generating
	 * @param $sort
	 * @return string
	 */
	public function renderOptions($sort = false)
	{
		$options = $this->options;
		if ($sort)
		{
			$this->sortData();
		}

		$this->deleteEmptyData();

		$i = 0;
		foreach($this->data as $key => $val_arr)
		{
			if ($i == 0)
			{
				$charts_cnt = count($val_arr);
			}
			$options['xAxis'][0]['categories'][] = $key;

			foreach($val_arr as $name => $val)
			{
				if ($i < $charts_cnt)
				{
					$options['yAxis'][$i]['title']['text'] = $name;
					$options['yAxis'][$i]['labels']['style']['color'] = $this->expr("Highcharts.getOptions().colors[$i]");
					$options['yAxis'][$i]['labels']['style']['font-weight'] = "bold";
					$options['yAxis'][$i]['opposite'] = true;
					$options['yAxis'][$i]['floor'] = 0;
					$options['series'][$i]['yAxis'] = $i;
					$options['series'][$i]['name'] = $name;
				}
				$options['series'][$i % $charts_cnt]['data'][] = $val;
				$i++;
			}
		}

		$res = json_encode($options);

		foreach ($this->expr as $expr)
		{
			$res = str_replace(json_encode($expr), $expr, $res);
		}

		return $res;
	}

	/**
	 * Chart generating from setted options
	 * @param $sort
	 * @param $localHighchartsPath
	 * @return string
	 */
	public function render($id, $localHighchartsPath = null)
	{
		return
			'<script src="//code.highcharts.com/' . self::$ver . '/highcharts.js"></script>' .
			(!is_null($localHighchartsPath) ? "<script>typeof $.fn.highcharts == 'function' || document.write('<script src=\"$localHighchartsPath\"><\/script>')</script>" : "") .
			'<script>$(function () {$("#' . $id . '").highcharts(' . $this->renderOptions(true) . ');});</script>';
	}
}
