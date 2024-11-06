<?php

namespace App\Events\Excel;

use Illuminate\Support\Facades\Storage;
use Shuchkin\SimpleXLSXGen;

class SimpleExtendXLSXGen extends SimpleXLSXGen
{
	public function saveAsExcel($folder, $filename)
	{
	    $temp_file = sys_get_temp_dir() . '/'. $filename;
		$fh = fopen($temp_file, 'w+');
		if (!$fh) {
			return false;
		}
		if (!$this->_write($fh)) {
			fclose($fh);
			return false;
		}
		fclose($fh);
        Storage::disk('public')->put($folder . '/' . $filename, file_get_contents($temp_file));
		unlink($temp_file);
		return true;
	}

}
