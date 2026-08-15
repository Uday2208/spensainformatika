<?php

return [
    // Public Routes
    ['method' => 'GET', 'url' => '', 'action' => 'HomeController@index'],
    ['method' => 'GET', 'url' => 'about', 'action' => 'HomeController@about'],
    ['method' => 'GET', 'url' => 'portfolio', 'action' => 'HomeController@portfolio'],
    
    // Auth Routes
    ['method' => 'GET', 'url' => 'login', 'action' => 'AuthController@showLogin'],
    ['method' => 'POST', 'url' => 'login', 'action' => 'AuthController@processLogin'],
    ['method' => 'GET', 'url' => 'logout', 'action' => 'AuthController@logout'],
    
    // Guru Routes (App)
    ['method' => 'GET', 'url' => 'app/guru/dashboard', 'action' => 'GuruController@dashboard', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'GET', 'url' => 'app/guru/kelas', 'action' => 'GuruController@kelas', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'POST', 'url' => 'app/guru/kelas/store', 'action' => 'GuruController@storeKelas', 'middleware' => ['auth', 'role:guru']],
    
    ['method' => 'GET', 'url' => 'app/guru/siswa', 'action' => 'GuruController@siswa', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'POST', 'url' => 'app/guru/siswa/store', 'action' => 'GuruController@storeSiswa', 'middleware' => ['auth', 'role:guru']],
    
    ['method' => 'GET', 'url' => 'app/guru/absensi', 'action' => 'GuruController@absensi', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'POST', 'url' => 'app/guru/absensi/store', 'action' => 'GuruController@storeAbsensi', 'middleware' => ['auth', 'role:guru']],
    
    ['method' => 'GET', 'url' => 'app/guru/rekap-absensi', 'action' => 'GuruController@rekapAbsensi', 'middleware' => ['auth', 'role:guru']],
    
    // Penilaian Kurikulum Merdeka
    ['method' => 'GET', 'url' => 'app/guru/penilaian', 'action' => 'GuruController@penilaian', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'POST', 'url' => 'app/guru/penilaian/store', 'action' => 'GuruController@storePenilaian', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'GET', 'url' => 'app/guru/penilaian/input', 'action' => 'GuruController@inputNilai', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'POST', 'url' => 'app/guru/penilaian/input/store', 'action' => 'GuruController@storeInputNilai', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'GET', 'url' => 'app/guru/penilaian/delete', 'action' => 'GuruController@deletePenilaian', 'middleware' => ['auth', 'role:guru']],
    
    // Rubrik Routes
    ['method' => 'GET', 'url' => 'app/guru/penilaian/rubrik', 'action' => 'GuruController@rubrik', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'POST', 'url' => 'app/guru/penilaian/rubrik/store', 'action' => 'GuruController@storeRubrik', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'GET', 'url' => 'app/guru/penilaian/rubrik/delete', 'action' => 'GuruController@deleteRubrik', 'middleware' => ['auth', 'role:guru']],
    // Web Profile Settings
    ['method' => 'GET', 'url' => 'app/guru/profile-settings', 'action' => 'GuruController@profileSettings', 'middleware' => ['auth', 'role:guru']],
    ['method' => 'POST', 'url' => 'app/guru/profile-settings/store', 'action' => 'GuruController@storeProfileSettings', 'middleware' => ['auth', 'role:guru']],
    
    // Siswa Routes (App)
    ['method' => 'GET', 'url' => 'app/siswa/dashboard', 'action' => 'SiswaController@dashboard', 'middleware' => ['auth', 'role:siswa']],
    ['method' => 'POST', 'url' => 'app/siswa/password/update', 'action' => 'SiswaController@gantiPassword', 'middleware' => ['auth', 'role:siswa']],
];
