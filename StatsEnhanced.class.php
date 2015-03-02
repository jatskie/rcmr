<?php
class StatsEnhanced
{
	public $errors;
	public $valid_filter_types;
	public $valid_stat_types;
	public $prefix;
	public $valid_curric_ids;
	
	public function __construct()
	{
		require_once('../../config.php');
		require_once('../../course/lib.php');
		ini_set('memory_limit', '500M');
		$this->prefix             = 'mdl_';
		$this->errors             = array();
		$this->valid_filter_types = array(
			'all',
			'directorate',
			'school',
			'curriculum area',
			'course'
		);
		$this->valid_stat_types   = array(
			'all',
			'views',
			'adds',
			'updates',
			'uploads',
			'deletes'
		);
		$this->valid_curric_ids   = "3,4,16,22,24,29,30,31,33,39,40,41,42,43,44,45,46,47,48,49,50,51,54,55,59,61,63,66,67,68,69,122,124,205,263,274,306,334,335,385,386,389,393,394,398,398,399,400,401,402,554,555,562";
		$this->checkStatsTableExists();
	}
	
	public function checkStatsTableExists()
	{
		global $DB;
		$query = "SELECT * FROM mdl_daily LIMIT 1";
		if (!$table_exists = $DB->execute($query)) 
		{
			echo "Moodle stats table doesn't exist, creating it now...<br />";
			$query = "CREATE TABLE " . $this->prefix . "report_daily (
                    id INT(10) unsigned NOT NULL AUTO_INCREMENT,
                    courseid INT(10) unsigned DEFAULT 0,
                    categoryid INT(10) unsigned DEFAULT 0,
                    time_start BIGINT(15) unsigned NOT NULL,
                    time_end BIGINT(15) unsigned NOT NULL,
                    role_id INT(10) unsigned,
                    stat_type VARCHAR(15) NOT NULL DEFAULT 'all',
                    activity INT(10) unsigned NOT NULL, 
                    locked TINYINT(1) unsigned DEFAULT '0', 
                    PRIMARY KEY(id)
                    ) ENGINE = MyISAM DEFAULT CHARSET=utf8";
			if (!$result = execute_sql($query, false)) 
			{
				$this->errors[] = 'statistics table could not be created';
			}
			$stats = "INSERT INTO " . $this->prefix . "report_daily (id, courseid, categoryid, time_start, time_end, role_id, stat_type, activity, locked) 
                    VALUES (113,0,0,1235865600,1238540399,2,'all',246,1),(112,0,0,1235865600,1238540399,3,'all',7812,1),(111,0,0,1235865600,1238540399,5,'all',30852,1),(110,0,0,1233446400,1235865599,1,'all',2715,1),(109,0,0,1233446400,1235865599,2,'all',690,1),(108,0,0,1233446400,1235865599,3,'all',7682,1),(107,0,0,1233446400,1235865599,5,'all',33380,1),(106,0,0,1230768000,1233446399,1,'all',3326,1),(105,0,0,1230768000,1233446399,2,'all',665,1),(104,0,0,1230768000,1233446399,3,'all',5929,1),(103,0,0,1230768000,1233446399,5,'all',30406,1),(102,0,0,1228089600,1230767999,1,'all',8635,1),(101,0,0,1228089600,1230767999,2,'all',453,1),(100,0,0,1228089600,1230767999,3,'all',8216,1),(99,0,0,1228089600,1230767999,5,'all',39576,1),(98,0,0,1225497600,1228089599,1,'all',5977,1),(97,0,0,1225497600,1228089599,2,'all',5032,1),(96,0,0,1225497600,1228089599,3,'all',11193,1),(95,0,0,1225497600,1228089599,5,'all',27435,1),(94,0,0,1222815600,1225497599,1,'all',279,1),(93,0,0,1222815600,1225497599,2,'all',948,1),(92,0,0,1222815600,1225497599,3,'all',648,1),(91,0,0,1222815600,1225497599,5,'all',4172,1),(114,0,0,1235865600,1238540399,1,'all',4183,1),(115,0,0,1238540400,1241132399,5,'all',43938,1),(116,0,0,1238540400,1241132399,3,'all',7309,1),(117,0,0,1238540400,1241132399,2,'all',314,1),(118,0,0,1238540400,1241132399,1,'all',3210,1),(119,0,0,1241132400,1243810799,5,'all',28070,1),(120,0,0,1241132400,1243810799,3,'all',4992,1),(121,0,0,1241132400,1243810799,2,'all',77,1),(122,0,0,1241132400,1243810799,1,'all',8032,1),(123,0,0,1243810800,1246402799,5,'all',42501,1),(124,0,0,1243810800,1246402799,3,'all',6951,1),(125,0,0,1243810800,1246402799,2,'all',2183,1),(126,0,0,1243810800,1246402799,1,'all',1958,1),(127,0,0,1246402800,1249081199,5,'all',88835,1),(128,0,0,1246402800,1249081199,3,'all',10242,1),(129,0,0,1246402800,1249081199,2,'all',5554,1),(130,0,0,1246402800,1249081199,1,'all',4570,1),(145,0,0,1249081200,1251759599,3,'all',5111,1),(144,0,0,1249081200,1251759599,2,'all',3898,1),(143,0,0,1249081200,1251759599,1,'all',1485,1),(151,0,0,1249081200,1251759599,5,'all',17075,1)";
			if (!$result = execute_sql($stats, false)) 
			{
				$this->errors[] = 'historical statistics could not be inserted';
			}
		}
	}
	
	public function getMonthYearByDate($date)
	{
		$my_formatted = date('F Y', $date);
		return $my_formatted;
	}
	
	public function getStats($start_date, $end_date, $stat_type = 'all', $filter_course = 0, $filter_category = 0)
	{
		global $DB;
		$query = "SELECT r.id, s.activity, s.locked FROM " . $this->prefix . "report_daily s 
                JOIN " . $this->prefix . "role r ON s.role_id = r.id 
                WHERE s.time_start = " . $start_date . " AND s.time_end = " . $end_date . " 
                    AND s.stat_type = '$stat_type'";
		if ($filter_course != 0) 
		{
			$query .= " AND s.courseid = $filter_course";
		} 
		else if ($filter_category != 0) 
		{
			$query .= " AND s.categoryid = $filter_category";
		} 
		else 
		{
			$query .= " AND s.courseid = $filter_course AND s.categoryid = $filter_category";
		}
		
		if ($found = $DB->get_records_sql($query)) 
		{
			$stats_returned = array();
			$total          = 0;
			$locked_status  = 1;
			foreach ($found as $stat_found) 
			{
				$stats_returned[$stat_found->id] = $stat_found->activity;
				$total += $stat_found->activity;
				$locked_status = $stat_found->locked;
			}
			$stats_returned[1]        = (isset($stats_returned[1])) ? $stats_returned[1] : 0;
			$stats_returned[2]        = (isset($stats_returned[2])) ? $stats_returned[2] : 0;
			$stats_returned[3]        = (isset($stats_returned[3])) ? $stats_returned[3] : 0;
			$stats_returned[5]        = (isset($stats_returned[5])) ? $stats_returned[5] : 0;
			$stats_returned['Total']  = $total;
			$link                     = $this->generateUpdateLink($start_date, $end_date, $stat_type, $filter_course, $filter_category);
			$stats_returned['Action'] = $link;
			return $stats_returned;
		} 
		else 
		{
			return FALSE;
		}
	}
	
	public function getCourseIdsFromCategory($filter_category = 0)
	{
		global $DB;
		if ($filter_category != 0 && is_numeric($filter_category)) 
		{
			$query = "SELECT id, path FROM " . $this->prefix . "course_categories WHERE path LIKE ('%" . $filter_category . "%') AND coursecount > 0";
			if ($subcats = $DB->get_records_sql($query)) 
			{
				$paths = array();
				
				foreach ($subcats as $cat) 
				{
					$paths[$cat->id] = $cat->path;
				}
				
				foreach ($paths as $key => $value) 
				{
					$path_ids = explode('/', $value);
					if (!in_array($filter_category, $path_ids)) 
					{
						unset($paths[$key]);
					}
				}
				
				foreach ($paths as $key => $value) 
				{
					$valid_subcats[] = $key;
				}
				
				$subcats_csv = implode(',', $valid_subcats);
				$query       = "SELECT id FROM " . $this->prefix . "course WHERE category IN (" . $subcats_csv . ")";
				
				try 
				{
					if ($course_ids = $DB->get_records_sql($query)) 
					{
						$courses = array();
						foreach ($course_ids as $cid) 
						{
							$courses[] = $cid->id;
						}
						
						$cids_csv = implode(',', $courses);
						return $cids_csv;
					} 
					else 
					{
						return false;
					}
				}
				catch (Exception $e) 
				{
				}
			} 
			else 
			{
				return false;
			}
		} 
		else 
		{
			return false;
		}
	}
	
	public function generateStats($start_date, $end_date, $stat_type = 'all', $filter_course = 0, $filter_category = 0)
	{
		global $DB;
		$count_ac = array();
		$query    = "SELECT id FROM " . $this->prefix . "role";
		if ($role_ids = $DB->get_records_sql($query)) 
		{
			foreach ($role_ids as $rid) 
			{
				$count_ac[$rid->id] = 0;
			}
			$query = $this->buildLogQuery($start_date, $end_date, $stat_type, $filter_course, $filter_category);
			if ($logs = $DB->get_records_sql($query)) 
			{
				$role = 0;
				foreach ($logs as $log) 
				{
					$query = "SELECT DISTINCT roleid FROM " . $this->prefix . "role_assignments WHERE userid = " . $log->userid . "";
					if ($roles = $DB->get_records_sql($query)) 
					{
						$all_roles = array();
						foreach ($roles as $role) 
						{
							$all_roles[] = $role->roleid;
						}
						$num_roles = count($all_roles);
						if ($num_roles > 1) 
						{
							if (in_array(1, $all_roles)) 
							{
								$role = 1;
							} 
							else if (in_array(2, $all_roles) || in_array(17, $all_roles)) 
							{
								$role = 2;
							} 
							else if (in_array(3, $all_roles) || in_array(15, $all_roles)) 
							{
								$role = 3;
							} 
							else if (in_array(4, $all_roles)) 
							{
								$role = 3;
							} 
							else if (in_array(5, $all_roles)) 
							{
								$role = 5;
							}
						} 
						else 
						{
							$role = $all_roles[0];
						}
						$count_ac[$role]++;
					} 
					else 
					{
					}
				}
				
				foreach ($count_ac as $key => $value) 
				{
					if (($key == 1 || $key == 2 || $key == 3 || $key == 5)) 
					{
						$stat_data             = new Object();
						$stat_data->courseid   = $filter_course;
						$stat_data->categoryid = $filter_category;
						$stat_data->time_start = $start_date;
						$stat_data->time_end   = $end_date;
						$stat_data->role_id    = $key;
						$stat_data->stat_type  = $stat_type;
						$stat_data->activity   = $value;
						$stat_data->locked     = $this->lockOrNot($start_date);
						$query                 = "SELECT id FROM " . $this->prefix . "report_daily WHERE time_start = $start_date AND time_end = $end_date AND role_id = $key AND stat_type = '$stat_type'";
						if ($filter_course != 0) 
						{
							$query .= " AND courseid = $filter_course";
						} 
						else if ($filter_category != 0) 
						{
							$query .= " AND categoryid = $filter_category";
						} 
						else 
						{
							$query .= " AND courseid = $filter_course AND categoryid = $filter_category";
						}
						
						if ($results = $DB->get_records_sql($query)) 
						{
							$row_id = '';
							foreach ($results as $result) 
							{
								$row_id = $result->id;
							}
							$stat_data->id = $row_id;
							if (!$DB->update_record('report_daily', $stat_data)) 
							{
								$this->errors[] = "Stat could not be updated (" . date('F Y', $start_date) . "), Role ID = $key";
							}
						} 
						else 
						{
							if (!$DB->insert_record('report_daily', $stat_data)) 
							{
								$this->errors[] = "Stat could not be inserted (" . date('F Y', $start_date) . "), Role ID = $key";
							}
						}
					}
				}
			} 
			else 
			{
			}
		} 
		else 
		{
			$this->errors[] = "No Moodle role ids found, can't produce stats";
		}
		
		if (count($this->errors) > 0) 
		{
			return false;
		} 
		else 
		{
			return true;
		}
	}
	
	public function buildMonths($days = 0)
	{
		global $DB;
		$timestamp = '';
		$query     = "SELECT time_start from " . $this->prefix . "report_daily ORDER BY time_start ASC LIMIT 1";
		if ($oldest_stat = $DB->get_records_sql($query)) 
		{
			foreach ($oldest_stat as $oldest) 
			{
				$timestamp = $oldest->time_start;
			}
		}
		
		if ($days != 0) 
		{
			$days_timestamp = $this->makeTimestampForPast($days);
			if ($days_timestamp > $timestamp) 
			{
				$timestamp = $days_timestamp;
			}
		}
		$oldest_date_month = date('m', $timestamp);
		$oldest_date_year  = date('y', $timestamp);
		$newest_date_month = date('m');
		$newest_date_year  = date('y');
		$total_months      = array();
		$month_counter     = $oldest_date_month;
		$year_counter      = $oldest_date_year;
		while ($year_counter <= $newest_date_year) 
		{
			if (strlen($month_counter) == 1) 
			{
				$month_counter = "0" . $month_counter;
			}
			
			if (strlen($year_counter) == 1) 
			{
				$year_counter = "0" . $year_counter;
			}
			
			$total_months[] = $month_counter . $year_counter;
			if ($month_counter == $newest_date_month && $year_counter == $newest_date_year) 
			{
				break;
			}
			
			if ($month_counter == 12) 
			{
				$month_counter = 0;
				$year_counter++;
			}
			
			$month_counter++;
		}
		return $total_months;
	}
	
	public function generateTsFromDate($hour = 0, $minute = 0, $second = 0, $month = 0, $day = 0, $year = 0)
	{
		date_default_timezone_set('Europe/London');
		$ts = mktime($hour, $minute, $second, $month, $day, $year);
		return $ts;
	}
	
	public function getNumDaysInMonth($date)
	{
		$thirty_day_months     = array(
			'09',
			'04',
			'06',
			'11'
		);
		
		$thirty_one_day_months = array(
			'01',
			'03',
			'05',
			'07',
			'08',
			'10',
			'12'
		);
		$month_val             = substr($date, 0, 2);
		$year_val              = substr($date, 2);
		$end_day               = '';
		if (in_array($month_val, $thirty_day_months)) 
		{
			$end_day = 30;
		} 
		else if (in_array($month_val, $thirty_one_day_months)) 
		{
			$end_day = 31;
		} 
		else 
		{
			$year      = substr($date, 2);
			$leap_year = (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0);
			$end_day   = ($leap_year) ? 29 : 28;
		}
		return $end_day;
	}
	
	public function getAllMonths($days = 365)
	{
		$total_months = $this->buildMonths($days);
		$mfl          = array();
		foreach ($total_months as $date) 
		{
			$month_val  = substr($date, 0, 2);
			$year_val   = substr($date, 2);
			$date       = $month_val . $year_val;
			$end_day    = $this->getNumDaysInMonth($date);
			$ts_start   = $this->generateTsFromDate(0, 0, 0, $month_val, 1, $year_val);
			$ts_end     = $this->generateTsFromDate(23, 59, 59, $month_val, $end_day, $year_val);
			$mfl[$date] = array(
				$ts_start,
				$ts_end
			);
		}
		if (count($mfl) > 0) 
		{
			return $mfl;
		} 
		else 
		{
			return false;
		}
	}
	
	public function getTimestampsForCurMonth()
	{
		$month_val  = date('m');
		$year_val   = date('y');
		$date       = "$month_val" . "$year_val";
		$end_day    = $this->getNumDaysInMonth($date);
		$ts_start   = $this->generateTsFromDate(0, 0, 0, $month_val, 1, $year_val);
		$ts_end     = $this->generateTsFromDate(23, 59, 59, $month_val, $end_day, $year_val);
		$timestamps = array(
			$ts_start,
			$ts_end
		);
		return $timestamps;
	}
	
	public function lockOrNot($start_date_ts = '')
	{
		$locked     = 0;
		$today      = time('now');
		$now_month  = date('m', $today);
		$now_year   = date('y', $today);
		$stat_month = date('m', $start_date_ts);
		$stat_year  = date('y', $start_date_ts);
		if (($now_month == $stat_month) && ($now_year == $stat_year)) 
		{
			$locked = 0;
		} 
		else 
		{
			$locked = 1;
		}
		return $locked;
	}
	
	/**
	 *   displayMonthlyStats
	 */
	
	public function displayMonthlyStats($days, $stat_type = 'all', $filter_course = 0, $filter_category = 0)
	{
		global $DB;
		$all_months = $this->getAllMonths($days);
		$all_months = array_reverse($all_months);
		$i          = 0;
		foreach ($all_months as $key => $value) 
		{
			$month    = $this->getMonthYearByDate($value[0]);
			$month_id = str_replace(' ', '_', strtolower($month));
			if ($i == 0) 
			{
				echo "<table cellpadding=\"7\" cellspacing=\"2\" border=\"1\" class=\"generaltable boxaligncenter\">
                        <tbody>\n
                        <tr><th class=\"header\">Month</th>\n";
				$role_ids = array(
					1,
					2,
					3,
					5
				);
				$names    = array();
				foreach ($role_ids as $role) 
				{
					$role_name = $DB->get_record('role', array(
						'id' => $role
					));
					$names[]   = $role_name->name;
				}
				
				$headers = array(
					$names[0],
					$names[1],
					$names[2],
					$names[3],
					'Total',
					'Action'
				);
				
				foreach ($headers as $header) 
				{
					echo "<th class=\"header\">$header</th>\n";
				}
			}
			if ($stats_returned = $this->getStats($value[0], $value[1], $stat_type, $filter_course, $filter_category)) 
			{
				$tr_class = ($i % 2) ? 'r0' : 'r1';
				echo "<tr class=\"$tr_class\" id=\"$month_id\"><td>$month</td>\n";
				foreach ($stats_returned as $value) 
				{
					if (is_numeric($value)) 
					{
						echo "<td style=\"text-align:center; width:85px;\" class=\"td_class\">" . number_format($value) . "</td>\n";
					} 
					else 
					{
						echo "<td style=\"text-align:center;\" class=\"td_class\">$value</td>\n";
					}
				}
				echo "</tr>\n";
			} 
			else 
			{
				$tr_class = ($i % 2) ? 'r0' : 'r1';
				echo "<tr class=\"$tr_class\" id=\"$month_id\"><td>$month</td>\n";
				for ($c = 0; $c <= 4; $c++) 
				{
					echo "<td style=\"text-align:center;\" class=\"td_class\">0</td>";
				}
				$link = $this->generateUpdateLink($value[0], $value[1], $stat_type, $filter_course, $filter_category);
				echo "<td style=\"text-align:center;\">$link</td>";
			}
			$i++;
		}
		echo "</tbody>\n";
		echo "</table>\n";
	}
	
	public function updateMonthlyTableRow($start_date = 0, $end_date = 0, $stat_type = 'all', $filter_course = 0, $filter_category = 0)
	{
		$updated_html = '';
		$month        = $this->getMonthYearByDate($start_date);
		$month_id     = str_replace(' ', '_', strtolower($month));
		$this_month   = $this->getMonthYearByDate(time());
		$updated_html .= "<td>$month</td>";
		if ($stats_returned = $this->getStats($start_date, $end_date, $stat_type, $filter_course, $filter_category)) 
		{
			unset($stats_returned['Action']);
			foreach ($stats_returned as $value) 
			{
				$updated_html .= "<td style=\"text-align:center;\" class=\"td_class\">$value</td>";
			}
		} 
		else 
		{
			for ($c = 0; $c <= 4; $c++) 
			{
				$updated_html .= "<td style=\"text-align:center;\" class=\"td_class\">0</td>";
			}
		}
		
		if ($month != $this_month) 
		{
			$update_link = $this->generateUpdateLink($start_date, $end_date, $stat_type, $filter_course, $filter_category);
			$updated_html .= '<td style="text-align:center">' . $update_link . '</td>';
		}
		
		if ($updated_html != '') 
		{
			return $updated_html;
		} 
		else 
		{
			return FALSE;
		}
	}
	
	/**
	 *   updateSingleCompareStat
	 */
	public function updateSingleCompareStat($start_date = 0, $end_date = 0, $stat_type = 'all', $filter_course = 0, $filter_category = 0)
	{
		$updated_html = '';
		if ($stats_returned = $this->getStats($start_date, $end_date, $stat_type, $filter_course, $filter_category)) 
		{
			$updated_html = '<img width="39" height="34" alt="Stat exists" src="images/tick.gif" />';
			$updated_html .= $update_link;
		} 
		else 
		{
			$update_link = $this->generateUpdateLink($start_date, $end_date, $stat_type, $filter_course, $filter_category);
			$updated_html .= $update_link;
		}
		
		if ($updated_html != '') 
		{
			return $updated_html;
		} 
		else 
		{
			return FALSE;
		}
	}
	
	public function buildLogQuery($start_date = 0, $end_date = 0, $stat_type = 'all', $filter_course = 0, $filter_category = 0)
	{
		$query        = "SELECT id, userid FROM " . $this->prefix . "log WHERE time > " . $start_date . " AND time < " . $end_date . "";
		$type_logins  = "'login'";
		$type_views   = "'view', 'view all', 'view discussion', 'view entry', 'view form', 'view forum', 'view forums', 'view grade', 'view graph', 'view report', 'view responses', 'view submission', 'view subscribers', 'view reports overview', 'view e-ilp stats', 'view target', 'view target comments', 'view attendance', 'view ilp list'";
		$type_adds    = "'add', 'add category', 'add contact', 'add discussion', 'add entry', 'add mod', 'add post'";
		$type_updates = "'update', 'update entry', 'update feedback', 'update grades', 'update mod', 'update post'";
		$type_uploads = "'upload'";
		$type_deletes = "'delete', 'delete attempt', 'delete discussion', 'delete entry', 'delete mod', 'delete post'";
		$type_all     = '';
		if (in_array($stat_type, $this->valid_stat_types)) 
		{
			switch ($stat_type) 
			{
				case 'logins':
					$in_sql = $type_logins;
					break;
				case 'views':
					$in_sql = $type_views;
					break;
				case 'adds':
					$in_sql = $type_adds;
					break;
				case 'updates':
					$in_sql = $type_updates;
					break;
				case 'uploads':
					$in_sql = $type_uploads;
					break;
				case 'deletes':
					$in_sql = $type_deletes;
					break;
				default:
					$in_sql = $type_all;
			}
			
			if ($stat_type != 'all') 
			{
				$query .= " AND action IN ($in_sql)";
			}
		}
		
		if ($filter_course != 0) 
		{
			$query .= " AND course = '$filter_course' ";
		} 
		else if ($filter_category != 0) 
		{
			$course_ids = $this->getCourseIdsFromCategory($filter_category);
			$query .= " AND course IN ($course_ids) ";
		}
		return $query;
	}
	
	public function generateUpdateLink($start_date = 0, $end_date = 0, $stat_type = 'all', $filter_course = 0, $filter_category = 0, $link_type = 'trends')
	{
		global $DB;
		$link = '';
		if ($start_date != 0 && $end_date != 0) 
		{
			$month      = $this->getMonthYearByDate($start_date);
			$this_month = $this->getMonthYearByDate(time());
			$query      = $this->buildLogQuery($start_date, $end_date, $stat_type, $filter_course, $filter_category);
			$query .= " LIMIT 1";
			$days       = (isset($_GET['days'])) ? $_GET['days'] : '';
			$days_param = ($days != '') ? '&amp;days=' . $days : '';
			try 
			{
				if ($logs = $DB->get_records_sql($query)) 
				{
					$query = "SELECT locked FROM " . $this->prefix . "report_daily WHERE time_start = $start_date AND time_end = $end_date AND stat_type = '$stat_type'";
					if ($filter_course != 0) 
					{
						$query .= " AND courseid = $filter_course LIMIT 1";
					} 
					else if ($filter_category != 0) 
					{
						$query .= " AND categoryid = $filter_category LIMIT 1";
					} 
					else 
					{
						$query .= " AND courseid = $filter_course AND categoryid = $filter_category LIMIT 1";
					}
					$locked = 0;
					try 
					{
						if ($records = $DB->get_records_sql($query)) 
						{
							foreach ($records as $record) 
							{
								$locked = $record->locked;
							}
						}
					}
					catch (Exception $e) 
					{
					}
					
					if ($locked == 0) 
					{
						$class_html = ($link_type == 'trends') ? ' class="update_link_trends"' : ' class="update_link_comparisons" ';
					} 
					else 
					{
						$class_html = ($link_type == 'trends') ? ' style="display: none"' : ' style="display: none" ';
					}
					$name_html = ($month == $this_month) ? ' name="current" ' : 'name=""';
					$link_base = ($link_type == 'trends') ? 'index.php' : 'compare.php';
					if ($filter_course != 0) 
					{
						$link = '<a href="index.php?action=update&amp;sd=' . $start_date . '&amp;ed=' . $end_date . $days_param . '&amp;stat_type=' . $stat_type . '&amp;course_id=' . $filter_course . '" ' . $class_html . ' ' . $name_html . '>update</a>';
					} 
					else if ($filter_category != 0) 
					{
						$link = '<a href="index.php?action=update&amp;sd=' . $start_date . '&amp;ed=' . $end_date . $days_param . '&amp;stat_type=' . $stat_type . '&amp;category_id=' . $filter_category . '" ' . $class_html . ' ' . $name_html . '>update</a>';
					} 
					else 
					{
						$link = '<a href="index.php?action=update&amp;sd=' . $start_date . '&amp;ed=' . $end_date . $days_param . '&amp;stat_type=' . $stat_type . '" ' . $class_html . ' ' . $name_html . '>update</a>';
					}
				} 
				else 
				{
					$link = 'no logs';
				}
			}
			catch (Exception $e) 
			{
			}
		} 
		else 
		{
			$link = 'invalid date';
		}
		return $link;
	}
	
	public function makeTimestampForPast($days)
	{
		$timestamp = '';
		$timestamp = mktime(0, 0, 0, date('m'), date('d') - $days, date('Y'));
		return $timestamp;
	}
	
	public function generateDaysFromMonth($month)
	{
		$days      = 0;
		$this_year = date('y', time('now'));
		while ($month >= 1) 
		{
			$month_val      = ($month < 10) ? '0' . $month : $month;
			$date           = $month_val . $this_year;
			$days_for_month = $this->getNumDaysInMonth($date);
			$days += $days_for_month;
			$month--;
		}
		return $days;
	}
	
	public function generateTimeSelect()
	{
		global $DB;
		$html = '<select name="days">';
		for ($i = 1; $i <= 11; $i++) 
		{
			$days          = $this->generateDaysFromMonth($i);
			$days          = $days - date('d', time('now'));
			$days_get_var  = (isset($_GET['days'])) ? $_GET['days'] : '';
			$selected_text = ($days_get_var == $days) ? ' selected="selected"' : '';
			$month_text    = ($i == 1) ? ' month' : ' months';
			$html .= '<option value="' . $days . '"' . $selected_text . '>' . $i . $month_text . '</option>';
		}
		
		$query = "SELECT time_start from " . $this->prefix . "report_daily ORDER BY time_start ASC LIMIT 1";
		if ($oldest_log = $DB->get_records_sql($query)) 
		{
			foreach ($oldest_log as $oldest) 
			{
				$timestamp = $oldest->time_start;
			}
			$oldest_date_year = date('Y', $timestamp);
		}
		
		$this_year = date('Y');
		$no_years  = $this_year - $oldest_date_year;
		for ($i = 1; $i <= $no_years; $i++) 
		{
			$days          = round($i * 365.242199, 0);
			$days          = $days - date('d', time('now'));
			$year_text     = ($i == 1) ? ' year' : ' years';
			$days_get_var  = (isset($_GET['days'])) ? $_GET['days'] : '';
			$selected_text = ($days_get_var == $days || ($days_get_var == '' && $i == 1)) ? ' selected="selected"' : '';
			$html .= '<option value="' . $days . '"' . $selected_text . '>' . $i . $year_text . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	public function getName($category_id = 0, $course_id = 0)
	{
		global $DB;
		if (is_numeric($category_id) && $category_id != 0) 
		{
			$name = $DB->get_record('course_categories', array(
				'id' => $category_id
			));
			if ($name->name != '') 
			{
				return $name->name;
			}
		} 
		else if (is_numeric($course_id) && $course_id != 0) 
		{
			$name = $DB->get_record('course', array(
				'id' => $course_id
			));
			if ($name->fullname != '') 
			{
				return $name->fullname;
			}
		}
	}
	
	public function getCategoryIdsForFilter($filter = '')
	{
		global $DB;
		$valid_compare_filters = array(
			'directorate',
			'school',
			'curriculum_area'
		);
		
		if ($filter != '' && in_array($filter, $valid_compare_filters)) 
		{
			$category_ids = array();
			switch ($filter) 
			{
				case 'directorate':
					$query = "SELECT id FROM " . $this->prefix . "course_categories " . "WHERE parent = 0 " . "AND name LIKE '%Directorate%' " . "ORDER BY sortorder ASC";
					if ($categories = $DB->get_records_sql($query)) 
					{
						foreach ($categories as $cat) 
						{
							$category_ids[] = $cat->id;
						}
					}
					break;
				case 'school':
					$query = "SELECT id FROM " . $this->prefix . "course_categories WHERE name LIKE ('School of%') ORDER BY sortorder";
					if ($categories = $DB->get_records_sql($query)) 
					{
						foreach ($categories as $cat) 
						{
							$category_ids[] = $cat->id;
						}
					}
					break;
				case 'curriculum_area':
					$query = "SELECT id, name FROM " . $this->prefix . "course_categories
                            WHERE id IN (" . $this->valid_curric_ids . ") ORDER BY name ASC";
					if ($categories = $DB->get_records_sql($query)) 
					{
						foreach ($categories as $cat) 
						{
							$category_ids[] = $cat->id;
						}
					}
					break;
			}
			return $category_ids;
		} 
		else 
		{
			return FALSE;
		}
	}
	
	public function getAvgForTimePeriod($days = 0, $category_id = 0, $course_id = 0)
	{
		global $DB;
		$months       = $this->getAllMonths($days);
		$empty_month  = FALSE;
		$empty_months = array();
		foreach ($months as $month => $timestamps) 
		{
			$total_activity = 0;
			if ($stats_exist = $this->getStats($timestamps[0], $timestamps[1], 'all', $course_id, $category_id)) 
			{
				$total_activity = $stats_exist['Total'];
			} 
			else 
			{
				$log_search_sql = $this->buildLogQuery($timestamps[0], $timestamps[1], 'all', $course_id, $category_id);
				$log_search_sql .= " LIMIT 1";
				try 
				{
					if ($logs_found = $DB->get_records_sql($log_search_sql)) 
					{
						$empty_months['logs_exist'][] = "$month";
					} 
					else 
					{
						$empty_months['no_logs'][] = "$month";
					}
				}
				catch (Exception $e) 
				{
				}
				$total_activity = NULL;
				$empty_month    = TRUE;
			}
			$months[$month]['total_activity'] = $total_activity;
		}
		$name = $this->getName($category_id, $course_id);
		if ($category_id != 0)
			$avg_id = $category_id;
		if ($course_id != 0)
			$avg_id = $course_id;
		if (!$empty_month) 
		{
			$num_months = count($months);
			$sum        = 0;
			foreach ($months as $month) 
			{
				$sum += $month['total_activity'];
			}
			$avg                       = round($sum / $num_months);
			$averages[$avg_id]['name'] = $name;
			$averages[$avg_id]['avg']  = $avg;
		} 
		else 
		{
			$num_empty  = count($empty_months['no_logs']) + count($empty_months['logs_exist']);
			$num_months = count($months) - $num_empty;
			$sum        = 0;
			foreach ($months as $month) 
			{
				$sum += $month['total_activity'];
			}
			$avg                                 = round($sum / $num_months);
			$averages[$avg_id]['name']           = $name;
			$averages[$avg_id]['avg']            = NULL;
			$averages[$avg_id]['incomplete_avg'] = $avg;
			$averages[$avg_id]['empty_months']   = $empty_months;
		}
		return $averages;
	}
	
	public function showIncompleteStats($category_id, $days)
	{
		$months_for_days = $this->getAllMonths($days);
		arsort($months_for_days);
		$html .= "<h3></h3>";
		$html .= '<table border="1" cellpadding="5"><tr>';
		foreach ($months_for_days as $month => $timestamps) 
		{
			$ts_start       = $timestamps[0];
			$month_readable = date('M Y', $ts_start);
			$html .= "<th>$month_readable</th>";
		}
		$html .= "</tr>";
		$html .= "<tr>";
		foreach ($months_for_days as $month => $timestamps) 
		{
			$stat_exists = (!in_array($month, $empty_months)) ? TRUE : FALSE;
			if (!$stat_exists) 
			{
				if ($avg_value == NULL) 
				{
					$incomplete  = TRUE;
					$update_link = "<a href=\"compare.php?action=update&amp;sd=" . $timestamps[0] . "&amp;ed=" . $timestamps[1] . "&amp;stat_type=all&amp;category_id=" . $key . "&amp;filter=" . $filter . "&amp;days=" . $days . "\" class=\"update_link_comparisons\">update</a>";
				} 
				else if ($avg_value == 'no logs') 
				{
					$update_link = 'no logs';
				}
			}
			$html .= ($stat_exists) ? '<img src="images/tick.gif" width="39" height="34" alt="Stat exists" />' : $update_link;
			$html .= '</td>';
		}
		$html .= "</tr>";
	}
	
	public function getSubcategoryIdsForCat($category_id = 0)
	{
		global $DB;
		if ($category_id != 0) 
		{
			$query = "SELECT id FROM " . $this->prefix . "course_categories WHERE parent = $category_id AND visible = 1 ORDER BY name ASC";
			if ($subcats = $DB->get_records_sql($query)) 
			{
				$sids = array();
				foreach ($subcats as $subcat) 
				{
					$sids[] = $subcat->id;
				}
				return $sids;
			} 
			else 
			{
				return FALSE;
			}
		}
	}
	
	public function getSubcatDataForCatID($category_id = 0)
	{
		global $DB;
		if ($category_id != 0) 
		{
			$query = "SELECT id, name FROM " . $this->prefix . "course_categories WHERE parent = $category_id AND visible = 1";
			if ($subcats = $DB->get_records_sql_menu($query)) 
			{
				return $subcats;
			} 
			else 
			{
				return FALSE;
			}
		}
	}
	
	public function generateBargraphImgFromLevel($level = 'filter', $filter = '', $days = '', $cat = '', $subcat = '', $subsubcat = '')
	{
		$img = '';
		switch ($level) 
		{
			case 'filter':
				$img = '<img src="bargraph.php?filter=' . $filter . '&amp;days=' . $days . '" id="compare_graph" />';
				break;
			case 'category':
				$img = '<img src="bargraph.php?filter=' . $filter . '&amp;cat=' . $cat . '&amp;days=' . $days . '" id="compare_graph" />';
				break;
			case 'subcategory':
				$img = '<img src="bargraph.php?filter=' . $filter . '&amp;cat=' . $cat . '&amp;subcat=' . $subcat . '&amp;days=' . $days . '" id="compare_graph" />';
				break;
			case 'subsubcategory':
				$img = '<img src="bargraph.php?filter=' . $filter . '&amp;cat=' . $cat . '&amp;subcat=' . $subcat . '&amp;subsubcat=' . $subsubcat . '&amp;days=' . $days . '" id="compare_graph" />';
				break;
		}
		return $img;
	}
	
	public function getCourseIdsForCat($category_id = 0)
	{
		global $DB;
		if (is_numeric($category_id) && $category_id != 0) 
		{
			$query = "SELECT id FROM " . $this->prefix . "course WHERE category = $category_id ORDER BY fullname ASC";
			if ($ids = $DB->get_records_sql($query)) 
			{
				$course_ids = array();
				foreach ($ids as $id) 
				{
					$course_ids[] = $id->id;
				}
				return $course_ids;
			} 
			else 
			{
				return FALSE;
			}
		}
	}
	
	public function updateStatsFromCron()
	{
		global $DB;
		$timestamps = $this->getTimestampsForCurMonth();
		$start_date = $timestamps[0];
		$end_date   = $timestamps[1];
		foreach ($this->valid_stat_types as $type) 
		{
			$this->generateStats($start_date, $end_date, $type, 0, 0);
		}
		mtrace("Main stats updated - all types");
		$cat_ids = array(
			3,
			4,
			16,
			20,
			22,
			24,
			29,
			30,
			31,
			33,
			39,
			40,
			41,
			42,
			43,
			44,
			45,
			46,
			49,
			50,
			51,
			54,
			55,
			58,
			59,
			61,
			63,
			66,
			67,
			68,
			69,
			73,
			74,
			75,
			76,
			111,
			112,
			113,
			114,
			115,
			116,
			117,
			118,
			119,
			120,
			121,
			122,
			123,
			124,
			125,
			126,
			127,
			128,
			205,
			263,
			274,
			306,
			333,
			334,
			335,
			384,
			385,
			386,
			387,
			389,
			393,
			394,
			395,
			398,
			399,
			400,
			401,
			402,
			406,
			554,
			555
		);
		
		foreach ($cat_ids as $cat_id) 
		{
			foreach ($this->valid_stat_types as $type) 
			{
				$this->generateStats($start_date, $end_date, $type, 0, $cat_id);
			}
		}
		
		$today   = time('now');
		$now_day = date('j', $today);
		
		if ($now_day == 1) 
		{
			$last_month_val = (date('m')) - 1;
			
			if (strlen($last_month_val) == 1) 
			{
				$last_month_val = "0" . $last_month_val;
			}
			
			$year_val = date('y');
			$date     = "$last_month_val" . "$year_val";
			$end_day  = $this->getNumDaysInMonth($date);
			$ts_start = $this->generateTsFromDate(0, 0, 0, $last_month_val, 1, $year_val);
			$ts_end   = $this->generateTsFromDate(23, 59, 59, $last_month_val, $end_day, $year_val);
			$query    = "UPDATE mdl_daily SET locked = 1 WHERE categoryid = 0 AND courseid = 0 and time_start = '$ts_start' and time_end = '$ts_end' and locked = 0";
			$DB->execute_sql($query);
			$csv_catids = implode(',', $cat_ids);
			$query      = "UPDATE mdl_daily SET locked = 1 WHERE categoryid IN ($csv_catids) AND courseid = 0 and time_start = '$ts_start' and time_end = '$ts_end' and locked = 0";
			$DB->execute_sql($query);
		}
		mtrace("Main Category stats updated");
		mtrace("All stats updated successfully!");
	}
	
	public function __destruct()
	{
		if (count($this->errors) > 0) 
		{
			echo '<div style="color:red;">';
			echo "<h2>Errors</h2>";
			echo '<ul>';
			foreach ($this->errors as $error) 
			{
				echo "<li>$error</li>";
			}
			echo '</ul>';
			echo '</div>';
		}
		$this->errors[] = array();
	}
}
?>