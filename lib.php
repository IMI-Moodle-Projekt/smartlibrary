<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin functions for the local_smartlibrary plugin.
 *
 * @package   local_smartlibrary
 * @copyright 2023 Your Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// In lib.php
function get_my_records() {
    global $DB;
    return $DB->get_records('moodle.course');
}

function local_smartlibrary_extend_navigation_course(navigation_node $navigation) {
  global $PAGE;
  if ($PAGE->course) {
    if (has_capability('local/smartlibrary:edit', context_course::instance($PAGE->course->id), )) {
      $PAGE->requires->js(new moodle_url('/local/smartlibrary/insertfield.js'));
  }}

  $competenciesnode = navigation_node::create(get_string('nav_name', 'local_smartlibrary'),
          new moodle_url('/local/smartlibrary/view.php', array('courseid' => $PAGE->course->id)),
          navigation_node::TYPE_SETTING,
          null,
          null,
          new pix_icon('i/competencies', ''));
  $navigation->add_node($competenciesnode);
}

/*function local_smartlibrary_course_view_observer(\core\event\course_viewed $event) {
  // Observer logic goes here
  error_log('Course viewed event observed in course ID: ' . $event->courseid);
}*/

function get_activity_ids_for_course($courseid) {
  global $DB;

  $sql = "SELECT cm.id
          FROM {course_modules} cm
          JOIN {modules} m ON m.id = cm.module
          JOIN {course_sections} cs ON cm.section = cs.id
          WHERE cm.course = :courseid AND cm.deletioninprogress = 0 AND cs.visible = 1 AND cm.visible = 1";

  $activities = $DB->get_records_sql($sql, array('courseid' => $courseid));
  return array_keys($activities);
}

function get_activity_name($activityId) {
  global $DB;
  error_log("Fetching name for activity ID: $activityId");

  $moduleType = $DB->get_field_sql(
      "SELECT name FROM {modules} WHERE id = (
          SELECT module FROM {course_modules} WHERE id = ?
      )",
      [$activityId]
  );

  if (!$moduleType) {
      return 'Unknown Module Type';
  }

  switch ($moduleType) {
      case 'assign':
          $tableName = 'assign';
          break;
      case 'quiz':
          $tableName = 'quiz';
          break;
      case 'forum':
          $tableName = 'forum';
          break;
      case 'resource':
          $activityName = $DB->get_field_sql(
              "SELECT name FROM {resource} WHERE id = (
                  SELECT instance FROM {course_modules} WHERE id = ?
              )",
              [$activityId]
          );
          return $activityName ? $activityName : 'Unnamed PDF/File';
      // Add more cases for other module types as needed
      default:
          return 'Unsupported Module Type';
  }

  try {
      $activityName = $DB->get_field_sql(
          "SELECT name FROM {{$tableName}} WHERE id = (
              SELECT instance FROM {course_modules} WHERE id = ?
          )",
          [$activityId]
      );
  } catch (dml_exception $e) {
      debugging('Error fetching activity name: ' . $e->getMessage(), DEBUG_DEVELOPER);
      return 'Error Fetching Name';
  }

  return $activityName ? $activityName : 'Unknown Activity';
}

/*
Function provided by beliefmedia: 
http://www.beliefmedia.com/create-keywords
*/
function beliefmedia_keywords($string, $min_word_length = 3, $min_word_occurrence = 2, $as_array = false, $max_words = 8, $restrict = false) {

    /*function keyword_count_sort($first, $sec) { //auskommentiert by bassel
      return $sec[1] - $first[1]; //auskommentiert by bassel
    }*/ //auskommentiert by bassel
 
    $string = preg_replace('/[^\p{L}0-9 ]/', ' ', $string);
    $string = trim(preg_replace('/\s+/', ' ', $string));
     
    $words = explode(' ', $string);
 
    /* 	
     Only compare to common words if $restrict is set to false
     Tags are returned based on any word in text
     If we don't restrict tag usage, we'll remove common words from array 
    */
 
    if ($restrict === false) {
       $commonWords = array('a','able','about','above','abroad','according','accordingly',
       'across','actually','adj','after','afterwards','again','against','ago','ahead',
       'ain\'t','all','allow','allows','almost','alone','along','alongside','already',
       'also','although','always','am','amid','amidst','among','amongst','an','and',
       'another','any','anybody','anyhow','anyone','anything','anyway','anyways',
       'anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around',
       'as','a\'s','aside','ask','asking','associated','at','available','away','awfully',
       'b','back','backward','backwards','be','became','because','become','becomes',
       'becoming','been','before','beforehand','begin','behind','being','believe','below',
       'beside','besides','best','better','between','beyond','both','brief','but','by','c',
       'came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly',
       'changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently',
       'consider','considering','contain','containing','contains','corresponding','could',
       'couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described',
       'despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done',
       'don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either',
       'else','elsewhere','end','ending','enough','entirely','especially','et','etc','even',
       'ever','evermore','every','everybody','everyone','everything','everywhere','ex',
       'exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first',
       'five','followed','following','follows','for','forever','former','formerly','forth',
       'forward','found','four','from','further','furthermore','g','get','gets','getting','given',
       'gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half',
       'happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello',
       'help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers',
       'herself','he\'s','hi','him','himself','his','hither','home','hopefully','how','howbeit',
       'however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch',
       'inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead',
       'into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just',
       'k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly',
       'least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking',
       'looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me',
       'mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover',
       'most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near',
       'nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless',
       'new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally',
       'not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok',
       'okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise',
       'ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular',
       'particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably',
       'provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent',
       'recently','regarding','regardless','regards','relatively','respectively','right','round','s','said',
       'same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems',
       'seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she',
       'she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow',
       'someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify',
       'specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than',
       'thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them',
       'themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll',
       'there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re',
       'they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though',
       'three','through','throughout','thru','thus','till','to','together','too','took','toward','towards',
       'tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing',
       'unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used',
       'useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want',
       'wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t',
       'we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter',
       'whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst',
       'whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing',
       'wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll',
       'your','you\'re','yours','yourself','yourselves','you\'ve','z','zero', 'a', 'about', 'above', 'after', 'again',
       'against', 'all', 'am', 'an', 'and', 'any', 'are', 'aren\'t', 'as', 'at', 'be', 'because', 'been', 'before',
       'being', 'below', 'between', 'both', 'but', 'by', 'can\'t', 'cannot', 'could', 'couldn\'t','did', 'didn\'t',
       'do', 'does', 'doesn\'t','doing', 'don\'t', 'down', 'during','each','few', 'for', 'from', 'further', 'had', 
       'hadn\'t', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'he\'s', 'her', 'here', 
       'here\'s', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'how\'s', 'i', 'i\'d', 'i\'ll', 'i\'m', 'i\'ve', 
       'if', 'in', 'into', 'is', 'isn\'t', 'it', 'it\'s', 'its', 'itself','let\'s', 'me', 'more', 'most', 'mustn\'t',
       'my', 'myself', 'no', 'nor', 'not', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'ought', 'our', 'ours',
       'ourselves', 'out', 'over', 'own','same', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 
       'so', 'some', 'such', 'than', 'that', 'that\'s', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 
       'there\'s', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'this', 'those', 'through', 'to', 'too', 
       'under', 'until', 'up', 'very', 'was', 'wasn\'t', 'we', 'we\'d', 'we\'ll', 'we\'re', 'we\'ve', 'were', 'weren\'t', 
       'what', 'what\'s', 'when', 'when\'s', 'where', 'where\'s', 'which', 'while', 'who', 'who\'s', 'whom', 'why', 'why\'s', 
       'with', 'won\'t', 'would', 'wouldn\'t', 'you', 'you\'d', 'you\'ll', 'you\'re', 'you\'ve', 'your', 'yours', 'yourself', 
       'yourselves', 'ask', 'be', 'become', 'begin', 'call', 'can', 'come', 'could', 'do', 'feel', 'find', 'get', 'give', 'go', 
       'have', 'hear', 'help', 'keep', 'know', 'leave', 'let', 'like', 'live', 'look', 'make', 'may', 'mean', 'might', 'move', 
       'need', 'play', 'put', 'run', 'say', 'see', 'seem', 'should', 'show', 'start', 'take', 'talk', 'tell', 'think', 'try', 
       'turn', 'use', 'want', 'will', 'work', 'would',  'able', 'bad', 'best', 'better', 'big', 'black', 'certain', 'clear', 
       'different', 'early', 'easy', 'economic', 'federal', 'free', 'full', 'good', 'great', 'hard', 'high', 'human', 'important', 
       'international', 'large', 'late', 'little', 'local', 'long', 'low', 'major', 'military', 'national', 'new', 'old', 'only', 
       'other', 'political', 'possible', 'public', 'real', 'recent', 'right', 'small', 'social', 'special', 'strong', 'sure', 'true', 
       'white', 'whole', 'young', 'again', 'already', 'always', 'back', 'better', 'best', 'even', 'ever', 'far', 'fast', 'hard', 'hardly', 
       'high', 'how', 'late', 'long', 'low', 'more', 'most', 'much', 'near', 'never', 'now', 'often', 'only', 'perhaps', 'quite', 'really', 
       'right', 'soon', 'still', 'then', 'there', 'together', 'too', 'very', 'well', 'yet',  'the', 'for', 'an', 'as', 'how', 'to', 'and', 
       'of', 'a', 'with', 'that', 'your', 'well', 'as', 'using', 'what', 'you\'ll', 'learn', 'check', 'test');
       $words = array_udiff($words, $commonWords,'strcasecmp');
    }
 
    /* Restrict Keywords based on values in the $allowedWords array */
    if ($restrict !== false) {
       $allowedWords =  array('engine','boeing','electrical','pneumatic','ice');
       $words = array_uintersect($words, $allowedWords,'strcasecmp');
    }
 
    $keywords = array();
 
    while(($c_word = array_shift($words)) !== null) {
 
      if (strlen($c_word) < $min_word_length) continue;
      $c_word = strtolower($c_word);
 
         if (array_key_exists($c_word, $keywords)) $keywords[$c_word][1]++;
         else $keywords[$c_word] = array($c_word, 1);
    }
 
    //usort($keywords, 'keyword_count_sort'); //auskommentiert by bassel
    $final_keywords = array();
 
    foreach ($keywords as $keyword_det) {
      if ($keyword_det[1] < $min_word_occurrence) break;
      array_push($final_keywords, $keyword_det[0]);
    }
     
   $final_keywords = array_slice($final_keywords, 0, $max_words);
 
  return $as_array ? $final_keywords : implode(', ', $final_keywords);
 }

function get_keywords($string) {
    // We can replace the keyword extraction with more sophisticated NLP here in the future
    return beliefmedia_keywords($string, 3, 1, true, 50, false);
}

function save_keywords_to_database($entryid, $keywords) {
  global $DB;

  // Your Moodle database table name
  $table_name = $DB->prefix . 'smartlib_learning_resources';

  // Update the database with the entered keywords
  $DB->execute("UPDATE {$table_name} SET keywords = :keywords WHERE id = :entryid", array('keywords' => $keywords, 'entryid' => $entryid));
}
