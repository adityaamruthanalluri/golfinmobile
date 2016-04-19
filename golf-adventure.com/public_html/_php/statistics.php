<?php

class Statistics { 

	public static function stats($period = null) { 
		include($_SERVER['DOCUMENT_ROOT'].'/_config/db.config.php');
		$db = new MysqliDb($server, $user, $password, $database);
		if (!is_array($period)) {
			$period['from'] = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$period['to'] = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "+1 day" ) );
		}
		
		$col = 'visit_date';
		$db->orderBy('visit_date','ASC');
		$first_date = $db->getOne('visits', $col);
		$first_date = $first_date['visit_date'];
		if ($period['from'] < $first_date) {
			$period['from'] = $first_date;
		}	
		
		$db->where('visit_date > "' . $period['from'] . '"');
		$db->where('visit_date < "' . $period['to'] . '"');
		//$db->where('visit_browser NOT LIKE "%Bot%"');
		$total_unique = $db->get('visits');
		
		$col = 'visit_id';
		$db->where('visit_date > "' . $period['from'] . '"');
		$db->where('visit_date < "' . $period['to'] . '"');
		$db->where('visit_browser LIKE "%Bot%"');
		$bots = $db->get('visits', null, $col);
		foreach ($bots as $key => $value) {
			$robots[] = $value['visit_id'];
		}
		if (!is_array($robots)) {
			$robots[0] = 0;
		}
		$db->where('pagehit_date > "' . $period['from'] . '"');
		$db->where('pagehit_date < "' . $period['to'] . '"');
		$db->where('pagehit_visitid', $robots,'NOT IN');
		$total_hits = $db->get('page_hits');
		
		$db->where('pagehit_date > "' . $period['from'] . '"');
		$db->where('pagehit_date < "' . $period['to'] . '"');
		$db->where('pagehit_visitid', $robots,'NOT IN');
		$db->where('pagehit_url LIKE "/admin%"');
		$admin = $db->get('page_hits');
		
		$html = '
				<div id="statistics_overview">
					<h3>' . constant('STATISTICS') . ' - ' . date('Y-m-d', strtotime($period['from'])) . ' - ' . date('Y-m-d', strtotime($period['to']) - 1) . '</h3>
					<p>' . constant('TOTAL_VISITS') . ': ' . count($total_unique) . '</p>
					<p>' . constant('TOTAL_HITS') . ': ' . count($total_hits) . '</p>
					<p>' . constant('HITS_PUBLIC') . ': ' . (count($total_hits) - count($admin)) . '</p>
					<p>' . constant('HITS_PER_VISIT') . ': ' . number_format(((count($total_hits) - count($admin)) / count($total_unique)), 2, ',', ' ') . '</p>
				</div>
			';
		return $html;
	}

}

?>