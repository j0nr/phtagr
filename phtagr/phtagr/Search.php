<?php

include_once("$phtagr_lib/Url.php");
/**
  @class Search Mapping between URLs, HTML forms and SQL queries.
  @todo Rename get_url to get_url
  @todo Rename get_form to get_form
*/
class Search extends Url
{

var $_tags;
var $_sets;

function Search($baseurl='')
{
  global $search;
  $this->Url($baseurl);
  $this->_tags=array();
  $this->_sets=array();

  $this->add_param('section', 'explorer');
  if ($search && $search->get_userid()>0)
    $this->set_userid($search->get_userid());
}

function set_imageid($imageid)
{
  $this->add_iparam('id', $imageid, null, 1);
}

/** Set the user id
  @param userid If the userid is not numeric, it converts the name to the id */
function set_userid($userid)
{
  global $user;
  if (!is_numeric($userid))
    $userid=$user->get_id_by_name($userid);
  $this->add_iparam('user', $userid, null, 1);
}

function get_userid()
{
  return $this->get_param('user', 0);  
}

function set_groupid($groupid)
{
  $this->add_iparam('group', $groupid, null, 0);
}

function set_visibility($visibility)
{
  switch ($visibility) {
  case 'group': 
  case 'member':
  case 'public':
    $this->add_param('visibility', $visibility);
    break;
  default:
    break;
  }
}

function add_tag($tag)
{
  if ($tag=='') return;
  array_push($this->_tags, $tag);
  $this->_tags=array_unique($this->_tags);
}

/** @param tag Tag name
  @return True of the search has already that given tag */
function has_tag($tag)
{
  for ($i=0; $i<count($this->_tags); $i++)
    if ($this->_tags[$i]==$tag)
      return true;
  return false;
}

/** @param tag Tag to delete
  @return True if the tag could be deleted. Otherwise false (e.g. if the tag
  could not be found) */
function del_tag($tag)
{
  for ($i=0; $i<count($this->_tags); $i++)
  {
    if ($this->_tags[$i]==$tag)
    {
      array_splice($this->_tags, $i, 1);
      $this->_tags=array_merge($this->_tags);
      return true;
    }
  }
  return false;
}

/** Sets the operator of tags
  @param tagop Must be between 0 and 2 */
function set_tagop($tagop)
{
  $this->add_iparam('tagop', $tagop, null, 1, 2);
}

function clear_tags()
{
  unset($this->_tags);
  $this->_tags=array();
}

function add_set($set)
{
  if ($set=='') return;
  array_push($this->_sets, $set);
  $this->_sets=array_unique($this->_sets);
}

/** @param set Set name
  @return True of the search has already that given set */
function has_set($set)
{
  for ($i=0; $i<count($this->_sets); $i++)
    if ($this->_sets[$i]==$set)
      return true;
  return false;
}

/** @param set Set to delete
  @return True if the set could be deleted. Otherwise false (e.g. if the set
  could not be found) */
function del_set($set)
{
  for ($i=0; $i<count($this->_sets); $i++)
  {
    if ($this->_sets[$i]==$set)
    {
      unset($this->_sets[$i]);
      $this->_sets=array_merge($this->_sets);
      return true;
    }
  }
  return false;
}

/** Sets the operator of sets
  @param setop Must be between 0 and 2 */
function set_setop($setop)
{
  $this->add_iparam('setop', $setop, null, 1, 2);
}

function clear_sets()
{
  unset($this->_sets);
  $this->_sets=array();
}

function set_location($location)
{
  $this->add_param('location', $location);
}

function get_location()
{
  return $this->get_param('location', null);
}
function del_location()
{
  $this->del_param('location');
}

function has_location()
{
  if ($this->get_param('location', null)!=null)
    return true;
  else 
    false;
}

function set_location_type($location_type)
{
  $this->add_iparam('location_type', $location_type, LOCATION_UNDEFINED, LOCATION_UNDEFINED, LOCATION_COUNTRY);
}

/** Convert input string to unix time. Currently only the format of YYYY-MM-DD
 * and an integer as unix timestamp is supported.
  @param date arbitrary date string
  @return Unix time stamp. False on error. */
function _convert_date($date)
{
  if (is_numeric($date) && $date >= 0)
    return $date;

  // YYYY-MM-DD
  if (strlen($date)==10 && strpos($date, '-')>0)
  {
    $s=strtr($date, '-', ' ');
    $a=split(' ', $s);
    $sec=mktime(0, 0, 0, $a[1], $a[2], $a[0]);
    return $sec;
  }
  return false;
}

function set_date_start($start)
{
  $start=$this->_convert_date($start);
  $this->add_iparam('start', $start, null, 1);
}

function set_date_end($end)
{
  $end=$this->_convert_date($end);
  $this->add_iparam('end', $end, null, 1);
}

/**
  @param pos If is less than 0 set it to 0 */
function set_pos($pos)
{
  if (!is_numeric($pos) || $pos<0)
    $this->del_param('pos');
  else
    $this->add_iparam('pos', $pos, null, 1);

  $pos=$this->get_param('pos', 0);
  $size=$this->get_page_size();
  $this->set_page_num(floor($pos / $size));
}

function get_pos()
{
  return $this->get_param('pos', 0);
}

function set_page_num($page)
{
  $this->add_iparam('page', $page, null, 1);
}

function get_page_num()
{
  return $this->get_param('page', 0);
}

/**
  @param size If 0, set it to default. */
function set_page_size($size)
{
  if (!is_numeric($size))
    $size=10;

  if ($size!=10)
    $this->add_iparam('pagesize', $size, 10, 2, 250);
  else
    $this->del_param('pagesize');
}

function get_page_size()
{
  return $this->get_param('pagesize', 10);
}

function set_orderby($orderby)
{
  if ($orderby=='-date' ||
      $orderby=='ranking' ||
      $orderby=='-ranking' ||
      $orderby=='voting' ||
      $orderby=='-voting' ||
      $orderby=='newest' ||
      $orderby=='-newest' )
    $this->add_param('orderby', $orderby);
  else 
    $this->del_param('orderby');
  
}

function get_orderby()
{
  $return=$this->get_param('orderby', 'date');
}

function del_orderby()
{
  $this->del_param('orderby');
}

/** Creates a search object from a URL */
function from_URL()
{
  parent::from_URL();

  $this->add_riparam('id', null, 1);
   
  if (isset($_REQUEST['tags']))
  {
    if (strpos($_REQUEST['tags'], ' ')>0)
    {
      foreach (split("[ ]",$_REQUEST['tags']) as $tag)
        $this->add_tag($tag);
    }
    else if (strpos($_REQUEST['tags'], "+")>0)
    {
      foreach (split("[+]",$_REQUEST['tags']) as $tag)
        $this->add_tag($tag);
    }
    else
      $this->add_tag($_REQUEST['tags']);
  }

  if (isset($_REQUEST['tagop']))
    $this->set_tagop($_REQUEST['tagop']);
  
  if (isset($_REQUEST['sets']))
  {
    if (strpos($_REQUEST['sets'], ' ')>0)
    {
      foreach (split("[ ]",$_REQUEST['sets']) as $set)
        $this->add_set($set);
    }
    else if (strpos($_REQUEST['sets'], "+")>0)
    {
      foreach (split("[+]",$_REQUEST['sets']) as $set)
        $this->add_set($set);
    }
    else 
      $this->add_set($_REQUEST['sets']);
  }
  if (isset($_REQUEST['setop']))
    $this->set_setop($_REQUEST['setop']);

  $this->add_rparam('location', PARAM_STRING, null);
  $this->add_riparam('location_type', null, LOCATION_UNKNOWN, LOCATION_COUNTRY);
    
  if (isset($_REQUEST['user']))
    $this->set_userid($_REQUEST['user']);
  if (isset($_REQUEST['group']))
    $this->set_groupid($_REQUEST['group']);
  if (isset($_REQUEST['visibility'])) 
    $this->set_visibility($_REQUEST['visibility']);

  if (isset($_REQUEST['start']))
    $this->set_date_start($_REQUEST['start']);
  if (isset($_REQUEST['end']))
    $this->set_date_end($_REQUEST['end']);
  $this->add_iparam('pos', $_REQUEST['pos'], null, 1);
  $this->add_iparam('page', $_REQUEST['page'], null, 1);

  if (isset($_REQUEST['pagesize']))
    $this->set_page_size($_REQUEST['pagesize']);
  
  if (isset($_REQUEST['orderby']))
    $this->set_orderby($_REQUEST['orderby']);
}

/** Sets the tag and set array as parameter */
function _to_params()
{
  $num_tags=count($this->_tags);
  if ($num_tags>0)
  {
    $v='';
    for ($i=0; $i<$num_tags; $i++)
    {
      $v.=$this->_tags[$i];
      if ($i<$num_tags-1)
        $v.='+';
    }
    $this->add_param('tags', $v);
  }
  else 
    $this->del_param('tags');
  
  $num_sets=count($this->_sets);
  if ($num_sets>0)
  {
    $v='';
    for ($i=0; $i<$num_sets; $i++)
    {
      $v.=$this->_sets[$i];
      if ($i<$num_sets-1)
        $v.='+';
    }
    $this->add_param('sets', $v);
  }
  else
    $this->del_param('sets');
}

/** Converts the search to a URL */
function get_url()
{
  $this->_to_params();
  return parent::get_url();
}


/** Print the search as a HTML form */
function get_form()
{
  $this->_to_params();
  return parent::get_form();  
}

/** Create a SQL query from a tag array 
  @param tags Array of tags, could be NULL
  @param sets Array of sets, could be NULL
  @param order Insert order column to select statement if true
  @return Return the sql statement of the query object corresponding to the
  Seach parameters */
function _get_query_from_tags($tags, $sets, $order=false)
{
  global $db;
  global $user;
  $num_tags=count($tags);
  $num_sets=count($sets);
    
  $sql="SELECT i.id";
  if ($order)
    $sql.=$this->_get_column_order();
  $tagop=$this->get_param('tagop', 0);
  if ($tagop==1 || $tagop==2)
    $sql.=", COUNT(i.id) AS hits";

  $sql.=" FROM $db->image AS i";
  if ($num_tags)
    $sql .= ",$db->imagetag AS it";
  if ($num_sets)
    $sql .= ",$db->imageset AS iset";
  $location=$this->get_param('location', '');
  if ($location!='') 
    $sql .= ",$db->imagelocation AS il";
    
  $sql .= " WHERE 1=1"; // dummy where clause
  
  // handle IDs of image
  $imageid=$this->get_param('id', 0);
  $userid=$this->get_param('user', 0);
  $groupid=$this->get_param('group', -1);

  if ($imageid>0)  $sql .= " AND i.id=".$imageid;
  if ($userid>0)   $sql .= " AND i.userid=".$userid;
  if ($groupid>=0) $sql .= " AND i.groupid=".$groupid;
  
  // handle the acl and visibility level
  $sql .= $this->_handle_acl();
  $sql .= $this->_handle_visibility();
  
  // handle tags
  if ($num_tags)
    $sql .= " AND i.id=it.imageid";
  if ($num_tags>1)
  {
    $sql .= " AND (";
    for ($i=0; $i<$num_tags; $i++)
    {
      $tagid=$db->tag2id($tags[$i]);
      $sql .= " it.tagid=$tagid";
      if ($i != $num_tags-1)
        $sql .= " OR";
    }
    $sql .= " )";
  }
  else if ($num_tags==1)
  {
    $tagid=$db->tag2id($tags[0]);
    $sql .= " AND it.tagid=$tagid";
  }

  // handle sets
  if ($num_sets)
    $sql .= " AND i.id=iset.imageid";
  if ($num_sets>1)
  {
    $sql .= " AND (";
    for ($i=0; $i<$num_sets; $i++)
    {
      $setid=$db->set2id($sets[$i]);
      $sql .= " iset.setid=$setid";
      if ($i != $num_sets-1)
        $sql .= " OR";
    }
    $sql .= " )";
  }
  else if ($num_sets==1)
  {
    $setid=$db->set2id($sets[0]);
    $sql .= " AND iset.setid=$setid";
  }

  // handle location
  if ($location!='')
  {
    $locationtype=$this->get_param('location_type', 0);
    $locationid=$db->location2id($location, $location_type);
    $sql .= " AND i.id=il.imageid AND il.locationid=$locationid";
  }

  // handle date
  $start=$this->get_param('start', 0);
  $end=$this->get_param('end', 0);
  if ($start>0)
    $sql .= " AND i.date>=FROM_UNIXTIME($start)";
  if ($end>0)
    $sql .= " AND i.date<FROM_UNIXTIME($end)";

  return $sql;
}

/** Returns sql statement for the where clause which checks the acl */
function _handle_acl()
{
  global $db;
  global $user;
  
  $acl='';
  if ($user->is_admin() || 
    $this->get_userid()==$user->get_id())
    return $acl;
    
  // if requested user id is not the own user id
  else if ($user->is_member() || $user->is_guest())
  {
    $acl .= " AND (
               (i.groupid in ( 
                SELECT groupid
                FROM $db->usergroup
                WHERE userid=".$user->get_id().")
              AND i.gacl>=".ACL_PREVIEW." )";
    if ($user->is_member())
      $acl .= " OR i.macl>=".ACL_PREVIEW;
    else
      $acl .= " OR i.aacl>=".ACL_PREVIEW;
    $acl .= " )";

  }
  else {
    $acl .= " AND i.aacl>=".ACL_PREVIEW;
  }

  return $acl;
}

/** Sets the visiblity of an image. It selects images which are only visible
 * for the group, only for members or visible for the public */
function _handle_visibility()
{
  $acl='';
  $visible=$this->get_param('visibility', '');
  switch ($visible) {
  case 'group':
    $acl .= " AND i.gacl>=".ACL_PREVIEW." AND i.macl<".ACL_PREVIEW; 
    break;
  case 'member':
    $acl .= " AND i.macl>=".ACL_PREVIEW." AND i.aacl<".ACL_PREVIEW; 
    break;
  case 'public':
    $acl .= " AND i.aacl>=".ACL_PREVIEW; 
    break;
  default:
    break;
  }
  return $acl;
}
/** 
  @return Returns the column order for the selected column. This is needed for
  passing the order from subqueries to upper queries.*/
function _get_column_order()
{
  $order='';
  $orderby=$this->get_param('orderby', 'date');
  switch ($orderby) {
  case 'date':
  case '-date':
    $order.=",date";
    break;
  case 'ranking':
  case '-ranking':
    $order.=",ranking";
    break;
  case 'voting':
  case '-voting':
    $order.=",voting,votes";
    break;
  case 'newest':
  case '-newest':
    $order.=",created";
    break;
  default:
    break;
  }

  return $order;
}

/** Adds a SQL sort statement 
  @return Retruns an SQL order by statement string */
function _handle_orderby()
{
  $hits='';
  $tagop=$this->get_param('tagop', 0);
  if ($tagop==1 || $tagop==2)
    $hits.=" hits DESC";

  $order='';
  $orderby=$this->get_param('orderby', 'date');
  switch ($orderby) {
  case 'date':
    $order.=" i.date DESC";
    break;
  case '-date':
    $order.=" i.date ASC";
    break;
  case 'ranking':
    $order.=" i.ranking DESC";
    break;
  case '-ranking':
    $order.=" i.ranking ASC";
    break;
  case 'voting':
    $order.=" i.voting DESC, i.votes DESC";
    break;
  case '-voting':
    $order.=" i.voting ASC, i.votes ASC";
    break;
  case 'newest':
    $order.=" i.created DESC";
    break;
  case '-newest':
    $order.=" i.created ASC";
    break;
  default:
    break;
  }
  if ($hits!='' && $order!='')
    return " ORDER BY".$hits.",".$order;
  else if ($hits!='')
    return " ORDER BY".$hits;
  else if ($order!='')
    return " ORDER BY".$order;
    
  return '';
}

/** 
  @param num_tags Count of tags. Should be zero or greater zero
  @param tagop Tag operand (0 is and, 1 is or, 2 is fuzzy)
  @return Returns the having statement */
function _handle_having($num_tags, $tagop)
{
  // handle tag operation
  if ($num_tags>1)
  {
    switch ($tagop) {
    case 0:
      $sql .= " HAVING COUNT(i.id)=$num_tags";
      break;
    case 1:
      //$sql .= " HAVING COUNT(i.id)>=1";
      break;
    case 2:
      $fuzzy=intval($num_tags*0.75);
      $sql .= " HAVING COUNT(i.id)>=$fuzzy";
      break;
    }
  }
  return $sql;
}

/** Adds the SQL limit statement 
  @param limit If 0 do not limit and return an empty string. If it is 1 the
  limit is calculated by page_size and page_num. If it is 2, the limit is set
  by pos and page_size.  Default is 0. 
  @return SQL limit string */
function _handle_limit($limit=0)
{
  $pos=$this->get_param('pos', 0);
  $page=$this->get_param('page', 0);
  $size=$this->get_page_size();

  if ($limit==1)
  {
    // Limit, use $count
    $pos=$page*$size;
    return " LIMIT $pos," . $size;
  }
  else if ($limit==2)
  {
    return " LIMIT $pos," . $size;
  }
  return '';
}

/** Returns the SQL query of the search. It splits the tags according to
 * positiv or negative tags (negative tags have a minus sign as prefix) and
 * creates subqueries for positive and negative tags.
  @param limit Type of limit the query. 0 means no limit. 1 means limit by page
  size and page num. And 2 means limit by pos and size. 
  @param order If this flag is true, the order column will be included into the
  select statement. Otherwise not. Default is true.
  @return SQL query string 
  @see _get_query_from_tags, _handle_limit, _get_column_order  */
function get_query($limit=1, $order=true)
{
  global $db;
  $pos_tags=array();
  $neg_tags=array();
  $tagop=$this->get_param('tagop', 0);
  foreach ($this->_tags as $tag)
  {
    if ($tag{0}=='-')
      array_push($neg_tags, substr($tag, 1));
    else
      array_push($pos_tags, $tag);
  }
  $num_pos_tags=count($pos_tags);
  $num_neg_tags=count($neg_tags);
  
  $pos_sets=array();
  $neg_sets=array();
  foreach ($this->_sets as $set)
  {
    if ($set{0}=='-')
      array_push($neg_sets, substr($set, 1));
    else
      array_push($pos_sets, $set);
  }
  $num_pos_sets=count($pos_sets);
  $num_neg_sets=count($neg_sets);
  
  if (($num_pos_tags || $num_pos_sets) && 
      ($num_neg_tags || $num_neg_sets))
  {
    $sql="SELECT id";
    if ($order)
      $sql.=$this->_get_column_order();
    $sql.=" FROM ( ";
    $sql.=$this->_get_query_from_tags($pos_tags, $pos_sets, $order);
    $sql.=" AND id NOT IN ( ";
    $sql.=$this->_get_query_from_tags($neg_tags, $neg_sets, false);
    $sql.=" ) ) AS i";
    $sql.=" GROUP BY i.id";
    $sql.=$this->_handle_having($num_pos_tags, $tagop);

    if ($order)
      $sql.=$this->_handle_orderby();
    $sql.=$this->_handle_limit($limit);
  }
  else 
  {
    $sql=$this->_get_query_from_tags($pos_tags, $pos_sets);
    $sql.=" GROUP BY i.id";
    $sql.=$this->_handle_having($num_pos_tags, $tagop);

    if ($order)
      $sql.=$this->_handle_orderby();
    $sql.=$this->_handle_limit($limit);
  }

  return $sql; 
}

/** Returns the SQL statement to return the count of the query. The query does
 * not order the result. */
function get_num_query()
{
  global $db;
  $sql="SELECT COUNT(*) FROM ( ";
  $sql .= $this->get_query(0, false);
  $sql .= " ) AS num";
  return $sql;
}

function get_popular_tags()
{
  global $db;

  $sql="SELECT t.name,COUNT(t.name) AS hits 
        FROM $db->tag AS t, $db->imagetag AS it, $db->image as i 
        WHERE t.id=it.tagid and it.imageid=i.id";
  $sql.=$this->_handle_acl();
  $sql.=" GROUP BY t.name 
        ORDER BY hits DESC LIMIT 0,50";
  
  return $db->query($sql);
}

}

?>
