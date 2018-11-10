## MANAIPE CMS
MANAPIE CMS는 MANAPIE가 직접 참여하는 프로젝트를 쉽게 개발할 수 있도록 만든 CMS입니다.
Laravel 프레임워크로 만들어져 PHP 7.1.3~ 환경에서 작동합니다.

# Install
1. 도메인을 ./public에 연결
2. ./ 디렉터리에 압축 풀기
3. .env.example을 .env 파일로 복사해서 데이터베이스 정보 입력
4. 터미널에서 composer install
5. 터미널에서 php artisan key:generate
6. 터미널에서 php artisan migrate
7. 도메인으로 접속해서 '환영합니다'가 뜨는지 확인
8. 관리자 계정, 사이트 기본 정보, 추가 가이드 링크 등 세팅

(CAFE 24에서는 ./public/ckeditor/plugins/doksoft_uploader 22L에 config['BasePrefix'] = '/home/hosting_users'; 추가해줘야 함)


## License
MANAPIE가 직접 개발에 참여하는 프로젝트에서만 사용할 수 있습니다.
CMS가 사용된 프로젝트의 목록은 MANAPIE에 의해 관리되고 있으며, 계약이 만료된 프로젝트에서도 계속 사용될 수는 있으나 타인에 의한 수정을 엄격이 금지합니다.

Only use for application initially made by MANAPIE.
