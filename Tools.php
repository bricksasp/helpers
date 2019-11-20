<?php
namespace bricksasp\helpers;

use Yii;
use bricksasp\base\Config;

/**
 * 工具函数
 */
class Tools extends \yii\helpers\ArrayHelper {
	/**
	 * 返回当前的毫秒时间戳
	 */
	public static function mstime() {
		list($tmp1, $tmp2) = explode(' ', microtime());
		return sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
	}

	/**
	 * 生成编号
	 */
	public static function get_sn($type = 0) {
		switch ($type) {
		case 1: //订单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 2: //支付单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 3: //商品编号
			$str = 'G' . substr(static::mstime() . rand(0, 5), 1);
			break;
		case 4: //单品编号
			$str = 'P' . substr(static::mstime() . rand(0, 5), 1);
			break;
		case 5: //售后单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 6: //退款单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 7: //退货单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 8: //发货单编号
			$str = $type . substr(static::mstime() . rand(0, 9), 1);
			break;
		case 9: //提货单号
			$str = 'T' . $type . substr(static::mstime() . rand(0, 5), 1);
			break;
		case 10: //文件编号
			$str = 'F' . $type . substr(static::mstime() . rand(0, 5), 1);
		case 11: //单品条码
			$str = $type . substr(static::mstime() . rand(0, 5), 1);
			break;
		default:
			$str = substr(static::mstime() . rand(0, 9), 1);
		}
		return $str;
	}

	/**
	 * 格式化数据化手机号码
	 */
	public static function format_number($number, $type = 1) {
		switch ($type) {
		case 1: //两位小数
			return sprintf("%.2f", $number);
			break;
		case 2: //手机号码
			return substr($number, 0, 5) . "****" . substr($number, 9, 2);
			break;

		default:
			return false;
			break;
		}
	}

	/**
	 * 判断是否手机号
	 * @param $mobile
	 * @return bool
	 */
	public static function is_mobile($mobile = '') {
		if (preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 秒转换为天，小时，分钟
	 * @param int $second
	 * @return string
	 */
	public static function secondConversion($second = 0) {
		$newtime = '';
		$d = floor($second / (3600 * 24));
		$h = floor(($second % (3600 * 24)) / 3600);
		$m = floor((($second % (3600 * 24)) % 3600) / 60);
		if ($d > '0') {
			if ($h == '0' && $m == '0') {
				$newtime = $d . '天';
			} else {
				$newtime = $d . '天' . $h . '小时' . $m . '分';
			}
		} else {
			if ($h != '0') {
				if ($m == '0') {
					$newtime = $h . '小时';
				} else {
					$newtime = $h . '小时' . $m . '分';
				}
			} else {
				$newtime = $m . '分';
			}
		}
		return $newtime;
	}

	/**
	 * 获取最近天数的日期和数据
	 * @param $day
	 * @param $data
	 * @return array
	 */
	public static function get_lately_days($day, $data) {
		$day = $day - 1;
		$days = [];
		$d = [];
		for ($i = $day; $i >= 0; $i--) {
			$d[] = date('d', strtotime('-' . $i . ' day')) . '日';
			$days[date('Y-m-d', strtotime('-' . $i . ' day'))] = 0;
		}
		foreach ($data as $v) {
			$days[$v['day']] = $v['nums'];
		}
		$new = [];
		foreach ($days as $v) {
			$new[] = $v;
		}
		return ['day' => $d, 'data' => $new];
	}

	/**
	 * 数组层级递归
	 */
	public static function limitless($data, $root_id = 0, $relation = 'parent_id', $key = 'id', $level = 0) {
		$arr = [];
		foreach ($data as $v) {
			if ($v[$relation] == $root_id) {
				$arr[] = $v;
				$arr = array_merge($arr, static::limitless($data, $relation, $key, $v[$key], $level + 1));
			}
		}
		return $arr;
	}

	public static function findChild(&$arr, $id, $relation) {
		$childs = [];
		foreach ($arr as $k => $v) {
			if ($v[$relation] == $id) {
				$childs[] = $v;
			}

		}
		return $childs;
	}

	/**
	 * 数组tree结构
	 * @param  [array] $data
	 */
	public static function build_tree($data, $root_id = 0, $relation = 'parent_id', $key = 'id', $childname = 'children') {
		$childs = static::findChild($data, $root_id, $relation);
		if (empty($childs)) {
			return null;
		}

		foreach ($childs as $k => $v) {
			$rescurTree = static::build_tree($data, $v[$key], $relation, $key);
			if (null != $rescurTree) {
				$childs[$k][$childname] = $rescurTree;
			}

		}
		return $childs;
	}
	/**
	 * 读取所有目录
	 */
	public static function read_all_dir($path, $deep = 0) {
		$result = [];
		$handle = opendir($path); //读资源
		if ($handle) {
			$file = readdir($handle);
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..' && !is_file($file)) {
					$cur_path = $path . DIRECTORY_SEPARATOR . $file;

					$result[] = $cur_path;
				}
			}
			closedir($handle);
		}
		return $result;
	}

	/**
	 * 读取所有文件
	 */
	public static function read_all_file($path) {
		$result = [];
		$handle = opendir($path); //读资源
		if ($handle) {
			$file = readdir($handle);
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..') {
					$cur_path = $path . DIRECTORY_SEPARATOR . $file;
					if (is_dir($cur_path)) {
						//判断是否为目录，递归读取文件
						$result = array_merge($result, self::read_all_dir($cur_path));
					} else {
						$result[] = $cur_path;
					}
				}
			}
			closedir($handle);
		}
		return $result;
	}

	/**
	 * 下载远程文件
	 * @param  string $url
	 * @param  [type] $name
	 * @param  string $path
	 * @return string
	 */
	public static function download_file($url, $name, $path = '') {

		$file = $path . '/' . $name;
		if (!file_exists($path)) {
			self::make_dir($path);
		}
		ob_start(); //打开输出
		try {
			@readfile($url); //输出内容
		} catch (Exception $e) {
			return false;
		}
		$content = ob_get_contents(); //得到浏览器输出
		ob_end_clean(); //清除输出并关闭
		file_put_contents($file, $content);
		return $file;
	}

	/**
	 * 递归生成目录
	 * @param  [type] $dir [description]
	 * @return [type]      [description]
	 */
	public static function make_dir($dir) {
		return is_dir($dir) or self::make_dir(dirname($dir)) and mkdir($dir, 0777);
	}

	/**
	 * tcp 通信
	 * @param  string $host 地址
	 * @param  string $cmd  [description]
	 * @param  [type] $data [description]
	 * @param  array  $ext  [description]
	 * @param  string $eof  结束标记
	 * @return [type]       [description]
	 */
	public static function srequest(string $host, string $cmd, $data, $ext = [], $eof = "\r\n\r\n") {
		$fp = stream_socket_client($host, $errno, $errstr);
		if (!$fp) {
			throw new Exception("stream_socket_client fail errno={$errno} errstr={$errstr}");
		}

		$req = [
			'cmd' => $cmd,
			'data' => $data,
			'ext' => $ext,
		];
		// $req = [
		//     'method'  => $cmd,
		//     'params' => $data,
		//     'ext' => $ext,
		// ];

		$data = json_encode($req) . $eof;
		fwrite($fp, $data);

		$result = '';
		while (!feof($fp)) {
			$tmp = stream_socket_recvfrom($fp, 1024);
			$pos = strpos($tmp, $eof);
			if ($pos !== false) {
				$result .= substr($tmp, 0, $pos);
				break;
			} else {
				$result .= $tmp;
			}
		}

		fclose($fp);
		return json_decode($result, true);
	}

	/**
	 * 数组字段格式化
	 * @param  array  $data
	 * @param  array  $rule ['filed'=>['json_decode',['###',true]]]
	 * @param  int  $fg 一维二维
	 * @return array
	 * @author  <[<bricksasp 649909457@qq.com>]>
	 */
	public static function format_array($data = [], $rule = [], $fg = 1) {
		if ($fg == 1) {
			$res[] = $data;
		} else {
			$res = $data;
		}

		$res = array_map(function ($item) use ($rule) {
			foreach ($rule as $field => $v) {
				if (empty($v[2])) {
					$ags = str_replace('###', $item[$field], $v[1]);
				} else {
					foreach ($v[1] as &$vv) {
						if (is_array($vv)) {
							$vv = str_replace('###', $item[$field], $vv);
						}

					}
					$ags = $v[1];
				}
				$item[$field] = call_user_func_array($v[0], $ags);
			}
			return $item;
		}, $res);
		return $fg == 1 ? $res[0] : $res;
	}

	/**
	 * 实现二维数组的笛卡尔积组合
	 * $input 要进行笛卡尔积的二维数组
	 * $callback 自定义拼接格式 参数 $p1 $p2 格式[key,val]
	 * $output 最终实现的笛卡尔积组合,可不写 默认格式 1,2,3
	 * @return array
	 * @author  <[<bricksasp 649909457@qq.com>]>
	 */
	public static function cartesian($input, $callback = null, $output = []) {
		//去除第一个元素
		$first = array_shift($input);
		//判断是否是第一次进行拼接
		if (count($output) > 1) {
			foreach ($output as $k => $v) {
				foreach ($first as $k2 => $v2) {
					//可根据具体需求进行变更
					if ($callback == null) {
						$output2[] = $v . ',' . $v2;
					} else {
						$output2[] = $callback([$k, $v], [$k2, $v2]);
					}
				}
			}
		} else {
			foreach ($first as $k => $v) {
				//可根据具体需求进行变更
				if ($callback == null) {
					$output2[] = $v;
				} else {
					$output2[] = $callback([$k, $v]);
				}
			}
		}

		//递归进行拼接
		if (count($input) > 0) {
			$output2 = self::cartesian($input, $callback, $output2);
		}
		//返回最终笛卡尔积
		return $output2;
	}

	/**
	 * 删除文件
	 * @param  string $path 文件路径
	 * @return bool
	 */
	public static function deleteFile($path) {
		if (file_exists($path)) {
			return @unlink($path);
		}
		return false;
	}

	/**
	 * 使用异常中断操作
	 */
	public static function exceptionBreak($msg, $status = 200) {
		throw new \yii\web\HttpException($status, $msg);
	}

	/**
	 * 数组中选取key
	 */
	public static function chooseKey(array $data, array $keys) {
		$a = [];
		foreach ($keys as $key) {
			$a[$key] = $data[$key];
		}
		return $a;
	}

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	public static function client_ip($type = 0, $adv = false) {
		$type = $type ? 1 : 0;
		static $ip = NULL;
		if ($ip !== NULL) {
			return $ip[$type];
		}

		if ($adv) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$pos = array_search('unknown', $arr);
				if (false !== $pos) {
					unset($arr[$pos]);
				}

				$ip = trim($arr[0]);
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (isset($_SERVER['REMOTE_ADDR'])) {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u", ip2long($ip));
		$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}

	/**
	 * 文件访问地址
	 */
	public static function file_address($path='',$doman='')
	{
		if ($path) {
			return ($doman ? $doman : Config::instance()->web_url) . $path;
		}
		return '';
	}
}
