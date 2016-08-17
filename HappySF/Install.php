<?php
namespace HappySF;

class Install {
	public static function postPackageInstall() {
		if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			// windows가 아닌 경우에만 cache 폴더의 권한을 777로 변경한다.
			chmod('./cache', 0777);
		}
	}
}
// EOF
