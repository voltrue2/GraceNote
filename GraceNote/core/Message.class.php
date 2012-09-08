<?
class Message {
	private static $timer = false;
	private static $msg = null;
	private static $no_style = 'border: 0; padding: 0; margin: 0; font-size: 9px; font-family: Verdana; font-weight: normal;';
	
	public static function init(){
		if (!self::$timer){
			self::$timer = new Timer(1);
		}
	}
	
	public static function register($msg){
		if (self::$timer){
			$timestamp = '<td nowrap="nowrap" style="'.self::$no_style.' color: #FF0000;">'.str_pad(count(self::$msg), 4, '0', STR_PAD_LEFT).':[&nbsp;'.substr(str_pad((self::$timer->get() * 1000), 6, '0', STR_PAD_RIGHT), 0, 6).'&nbsp;ms&nbsp;]&nbsp;</td>';
			self::$msg[] = $timestamp.'<td style="'.self::$no_style.' text-align: left;">'.$msg.'</td>';
		}
	}
	
	public static function show(){
		if (self::$msg){
			echo('<table cellspacing="0" cellpadding="0" border="0">');
			foreach (self::$msg as $item){
				echo('<tr style="border-bottom: 1px solid #dddddd; padding-bottom: 1px; margin-bottom: 2px;">');
				echo($item);
				echo('</tr>');
			}
			echo('</table>');
		}
	}
}
?>
