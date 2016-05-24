<?php

/**
 *
 * Class ResumeParser
 */
class ResumeParser
{
		public static function convertXmlToArray($xmlstr)
		{
				$cv_data = array();

				try {
						$stringData = str_replace('xmlns="http://ns.hr-xml.org/2006-02-28"', 'xmlns=""', $xmlstr);
						$stringData = str_replace('xsi:schemaLocation="http://ns.hr-xml.org/2006-02-28 Resume.xsd http://sovren.com/hr-xml/2006-02-28 SovrenResumeExtensions.xsd"', '', $stringData);
						$stringData = str_replace('<sov:', '<', $stringData);
						$stringData = str_replace('</sov:', '</', $stringData);
						$xml        = new SimpleXMLElement($stringData);

				} catch (Exception $e) {
						echo $e->getMessage();
						echo $e->libxml_get_errors();
						return false;
				}


				if (!empty($xml->Personal)) {
						$cv_data['first_name']   = (string)$xml->Personal->FirstName;
						$cv_data['last_name']    = (string)$xml->Personal->LastName;
						$c                       = $xml->Personal->Address;
						$cv_data['country']      = strtolower($c->CountryCode); // country
						$cv_data['region']       = (string)$c->RegionCodeDescription; // region
						$cv_data['city']         = (string)$c->City; // city
						$cv_data['postcode']     = (string)$c->PostalCode;
						$cv_data['address1']     = (string)$c->StreetNumberBase . " " . (string)$c->StreetName;
						$cv_data['phone']        = self::cleanPhone($xml->Personal->HomePhones->HomePhone);
						$cv_data['mobile_phone'] = self::cleanPhone($xml->Personal->MobilePhones->MobilePhone);
						$cv_data['email']        = (string)$xml->Personal->Emails->Email;
				}

				if (!empty($xml->EmploymentHistory)) {
						$history  = '';
						$ehistory = $xml->EmploymentHistory->EmploymentItem;
						if ($ehistory)
								foreach ($ehistory as $ee) {
										if (trim($ee->StartDate) and trim($ee->EndDate)) {
												$history .= "Start: " . self::udate($ee->StartDate) . "\n";
												if ($ee->EndDate == "__NOWSTRING__")
														$history .= "End: Present\n";
												else
														$history .= "End: " . self::udate($ee->EndDate) . "\n";
										}
										$history .= (string)$ee->EmployerName . "\n";
										$history .= (string)$ee->JobTitle . "\n" . (string)$ee->Description . "\n\n";
								}

						$cv_data['UCCurrentDuties'] = $history;
				}

				if (!empty($xml->EducationHistory)) {
						$education  = '';
						$eeducation = $xml->EducationHistory->EducationItem;
						if ($eeducation)
								foreach ($eeducation as $e) {
										if (trim($e->InstituteName)) {
												if (trim($e->StartDate) and trim($e->EndDate)) {
														$education .= "Start: " . self::udate($e->StartDate) . "\n";
														$education .= "End: " . self::udate($e->EndDate) . "\n";
												} else {
														if (trim($e->StartDate) and $e->StartDate <> "__NOWSTRING__") $education .= self::udate($e->StartDate);
														if (trim($e->EndDate) and $e->EndDate <> "__NOWSTRING__") $education .= self::udate($e->EndDate);
														$education .= "\n";
												}
												$education .= $e->InstituteName . " " . $e->DegreeDirection . "\n";
												if (!stristr($e->DiplomaCodeDescription, 'Unknown')) $education .= (string)$e->DiplomaCodeDescription;
												if ($e->Subjects) $education .= $e->Subjects . "\n";
												$education .= "\n\n";
										}
								}

						$eeducation = $xml->Skills->ComputerSkills;
						if ($eeducation)
								$education .= "Computer Skills:\n";
						foreach ($eeducation as $e) {
								if ($e->ComputerSkill) {
										$education .= $e->ComputerSkill->ComputerSkillName . "\n";
										$education .= "\n\n";
								}
						}
				}
				$eeducation = $xml->Skills->ComputerSkills;
				if ($eeducation)
						$education .= "Computer Skills:\n";
				foreach ($eeducation as $e) {
						if ($e->ComputerSkill) {
								$education .= $e->ComputerSkill->ComputerSkillName . "\n";
								$education .= "\n\n";
						}
				}

				$eeducation = $xml->Skills->LanguageSkills;
				if ($eeducation)
						$education .= "Language Skills:\n";
				foreach ($eeducation as $e) {
						if ($e->LanguageSkill) {
								$education .= $e->LanguageSkill->LanguageSkillCodeDescription . "\n";
								$education .= "\n\n";
						}
				}

				$eeducation = $xml->Skills->SoftSkills;
				if ($eeducation)
						$education .= "Soft Skills:\n";
				foreach ($eeducation as $e) {
						if ($e->SoftSkill) {
								$education .= $e->SoftSkill->SoftSkillName . "\n";
								$education .= "\n\n";
						}
				}

				$cv_data['UCNMCNotes'] = $education;
				$cv_data['UCKeyWord2'] = $xmlstr;

				if (trim($xml->Personal->DateOfBirth))
						$cv_data['UCBirthDay'] = (string)$xml->Personal->DateOfBirth;
				$cv_data['gender'] = (string)$xml->Personal->GenderCode;

				if (!empty($c->CountryNode)) {
						$cv_data['CountryCode'] = (string)$c->CountryCode;
				}

				return $cv_data;

		}

		/**
		 * prettify phone #
		 * @param $x
		 *
		 * @return string
		 */
		public static function cleanPhone($x)
		{
				$x = ltrim($x, '0');
				$x = str_replace("(0)", "", $x);
				return "+" . $x;
		}

		/**
		 *
		 * prettify date (UK format)
		 *
		 * @param            $date
		 * @param bool|false $time
		 * @param bool|true  $convert
		 *
		 * @return bool|string
		 */
		public static function udate($date, $time = false, $convert = true)
		{
				$format = $time ? "jS F Y H:i" : "jS F Y";
				return ($date && $date != '0000-00-00 00:00:00') ? date($format, ($convert) ? strtotime($date) : $date ) : 'N/A';
		}
}