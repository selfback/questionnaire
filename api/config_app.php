<?php
	/** DEFAULT LANGUAGE */
	define('DEFAULT_LANG', 'en');

	define('SB_LOG_INFO', 'INFO');
	define('SB_LOG_WARNING', 'WARNING');
	define('SB_LOG_ERROR', 'ERROR');

	//Europe/Oslo
	//Europe/Paris
	define('TIMEZONE', 'Europe/Oslo');

	define('USER_ROLE', 'ROLE_USER');

	define('EMAIL_REGEX', '^[0-9a-z.+_-]+@{1}[0-9a-z.-]{2,}[.]{1}[a-z]{2,5}$');
	define('PHONE_REGEX', '\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$');

	define('LANG_TAB', serialize([
		'da' => 'Danish',
		'en' => 'English',
		'fr' => 'French',
		'no' => 'Norwegian'
		]));

	define('PAGES', serialize([
        "01_instruction.php",
        "02_profile.php",
		"03_familly.php",
		"04_ethnicity.php",
		"05_education.php",
		"06_employment.php",
		"07_workCharacteristics.php",
		"08_painLastWeek.php",
		"09_durationCurrentLowBackPain.php",
		"10_lowBackPainLastYear.php",
		"11_activityLimitation1.php",
		"12_painMedication.php",
		"13_activityLimitation2.php",
		"14_beliefsLowBackPain.php",
		"15_confidence.php",
		"16_perceptions.php",
		"17_function.php",
		"18_mannequin.php",
		"19_otherDiseasesConditions.php",
		"20-1_qualityOfLife.php",
		"20-2_qualityOfLifeScale.php",
		"21_physicalActivity.php",
		"22_sleep.php",
		"23_mood1.php",
		"24_mood2.php",
		"25_thoughts.php",
		"final.php"
	]));
?>
