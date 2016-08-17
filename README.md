# 소개
[행복한책읽기](http://happysf.net) [커뮤니티 게시판](http://happysf.net/zeroboard/zboard.php?id=reader)을 RSS feed로 제공하는 스크립트입니다.

# 필요 요소
- php 5.5 이상
- [composer](https://getcomposer.org) 가 설치되어 있어야 합니다.

# 설치
	> git clone https://github.com/kirrie/happsf-rss.git happysf-rss
	> cd happysf-rss
	> composer install

# 기타
- 게시판 내용을 기본 1시간 동안 로컬 파일시스템에 캐시합니다. 이 값은 config.yaml에서 변경할 수 있습니다.
- 현재 행복한책읽기 웹사이트 전체가 해외로부터의 접근이 막혀 있으므로, 아마존과 같이 해외에 존재하는 서버에 설치할 경우 정상적으로 동작하지 않습니다.
