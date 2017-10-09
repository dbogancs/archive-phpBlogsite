<?php
	
	function hunsort($a, $b)
	{
		static $Hchr = array('á'=>'az', 'é'=>'ez', 'í'=>'iz', 'ó'=>'oz', 'ö'=>'ozz', 'ő'=>'ozz', 'ú'=>'uz', 'ü'=>'uzz', 'ű'=>'uzz',  'Á'=>'az', 'É'=>'ez', 'Í'=>'iz', 'Ó'=>'oz', 'Ö'=>'ozz', 'Ő'=>'ozz', 'Ú'=>'uz', 'Ü'=>'uzz', 'Ű'=>'uzz');
		
		$a = strtr($a,$Hchr);   $b = strtr($b,$Hchr);
		$a=strtolower($a); $b=strtolower($b);
		
		return strcmp($a, $b);
	}
	?>