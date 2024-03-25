<?php
namespace Flyspray;

use Filters;


/**
* just an idea... most parts not implemented
*
* I am really unsure how customized for Flyspray or abstract/general it should be.
*
* For Flyspray 1.0 only 3 changing operations needed:
* addChildNode (append rightmost as child of parent node)
* deleteNode (keeping subtrees)
* and the update that updates a whole tree submitted from frontend (lft,rgt,category_name,category_owner,show_in_list)
*
* Variants of database 'Nested Set Model' in the wild quite differ: some store also redundant information in columns like:
* - level of node
* - parent_id (like an added adjacency tree model)
* - mark entries as softdelete - this is a bit similiar to show_in_list column in Flysprays {list_category}
*/
class CategoriesNestedSetDB
{
	/**
	* tablename optional surrounded by {} that allows prefixing table names replaced the $db wrapper
	*/
	public $table = '{list_category}';

	/**
	* db column name used to differentiate between trees in the same db table
	*/
	public $f_treeid = 'project_id';

	/**
	* db column that contains the node id. This id are unique across all trees as this is an autoincrement serial id.
	*/
	public $f_id = 'category_id';

	// not set in favor of SQL query readability. Assumes all nested set model tables use lft and rgt as fieldnames..
	//public $f_lft = 'lft';
	//public $f_rgt = 'rgt';

	public $f_show = 'show_in_list';

	/**
	* Names are not unique.
	*
	* The name should be unique on the same level in a subtree. (sibling should not have the same name)
	*
	* That should be checked before update/move/add and even delete node, but extra sql query.
	* Why even for delete? Well the children of deleted node walk a level towards tree root, so probability exists there is a category with same name on that level.
	*
	* Is there a need to make them unique per a whole tree?
	*/
	public $f_name = 'category_name';

	/*
	* Not used yet!
	*
	* Used for validity check of the category_name is somehow unique
	*
	* 'table' per whole table like a unique index (category_name) in SQL
	* 'tree' per tree/project like a unique index (project_id,category_name) in SQL
	* 'sibling' must be unique within direct childs of a parentnode
	* 'path' must be unique along paths to root
	* 'no' do not care if names duplicated (for trees where a category_name is not needed and category_id is sufficient)
	*/
	public $dupchecktype='sibling';

	/*
	* the project_id the tree belongs to, 0 is the global category tree
	*/
	private $treeid;
	/*
	* db connection wrapper
	*/
	private $db;

	public function __construct(&$db, $treeid)
	{
		$this->db = &$db;
		$this->treeid = $treeid;

		/**
		* @todo Test if:
		* - table type supports transactions (mysql myisam/innodb..)
		* - db connection mode autocommit true/false
		*/
	}

	/**
	* checks for a single tree
	* @todo
	*/
	public function isValid()
	{
		return false;
	}

	/**
	* @todo
	*/
	public function hasGaps()
	{
		return true;
	}

	/**
	* @todo
	*/
	public function closeGaps()
	{
		return false;
	}

	/** add childen at rightmost position inside parentnode
	* @param int $parentid
	* @param string $name (optional)
	* @param int $show (optional)
	*/
	public function addChildNode($parentid=null, $name='', $show=0)
	{
		/** TODO if possible
		* (0) start transaction
		* 1. get lft and rgt of parent node
		* 2. make space for new node
		* 3. insert node
		* (4) commit/rollback
		*/
		if ($parentid>0) {
			$pnoderesult=$this->db->query('SELECT lft, rgt, '
				.$this->f_treeid.' AS project_id, '
				.$this->f_id.' AS category_id, '
				.$this->f_name.' AS category_name'
				.' FROM '.$this->table
				.' WHERE '.$this->f_treeid.'=? AND '.$this->f_id.'=?',
				array($this->treeid, $parentid)
			);
		} else {
			// get root node of tree $treeid
			$pnoderesult=$this->db->query('SELECT lft, rgt, '
				.$this->f_treeid.' AS project_id, '
				.$this->f_id.' AS category_id, '
				.$this->f_name.' AS category_name'
				.' FROM '.$this->table
				.' WHERE '.$this->f_treeid.'=? AND lft=1 AND '.$this->f_name."='root'",
				array($this->treeid)
			);
		}

		// if no row: node does not exist or project_id != $treeid
		if ($this->db->countRows($pnoderesult)) {
			$parent=$this->db->fetchRow($pnoderesult);
			#echo "before:";
			#print_r($parent);
			# TODO transaction if possible by database table engine
			$this->db->query('UPDATE '.$this->table.' SET rgt=rgt+2 WHERE '.$this->f_treeid.'=? AND rgt>=?', array($this->treeid, $parent['rgt']));
			$this->db->query('UPDATE '.$this->table.' SET lft=lft+2 WHERE '.$this->f_treeid.'=? AND lft> ?', array($this->treeid, $parent['rgt']));
			$this->db->query('INSERT INTO '.$this->table.'('.$this->f_treeid.', lft, rgt, '.$this->f_name.', '.$this->f_show.') VALUES(?,?,?,?,?)',
				array($this->treeid, $parent['rgt'], ($parent['rgt']+1), $name, ($show?1:0))
			);
			// commit/rollback if possible by database table engine
			return $this->db->insert_id();
		} else {
			return false;
		}
	}


	/*
	* adds a node beside an existing node. By default after the sibling
	* @param int $siblingid
	* @param bool $before (optional)
	*
	* @todo
	*/
	public function addSiblingNode($siblingid, $before=false)
	{
		return false;
	}

	/**
	* moves a single node without its childs/subtree or node with subtree
	*
	* @param int $nodeid
	* @param int $targetnodeid
	* @param int $withsubtree (optional) Default is true with subtree
	* @param bool $assibling false: targetnodeid is a parentnode; true: targetnodeid is a sibling
	* @param bool $before if the node is added leftmost or rightmost (children) or left or right (sibling)
	*
	* @todo
	*/
	public function moveNode($nodeid, $targetnodeid, $withsubtree=true, $assibling=false, $before=false)
	{
		return false;
	}

	/**
	* @param int $nodeid
	* @param bool $withsubtree if all descendents also be to deleted. Default is to keep them.
	*
	* @todo
	*/
	public function deleteNode($nodeid, $withsubtree=false)
	{
		return false;
	}
}

/**
* works completely different than the DB variant of Nested Set Model
* maybe helpful for a single tree validation
*
* needs a bit preparation if it gets the update POST request from Flyspray's edit Categories form.
*/
class CategoriesNestedSetArray
{
	public $tree;

	/**
	* @param array $treearray
	*/
	public function __construct($treearray)
	{
		$this->tree = $treearray;
	}

	/**
	* Does various test on the $tree if it is in its own world a valid nested set tree.
	* Does not mean it is a valid Flyspray project category tree!
	* @todo
	*/
	public function isValid()
	{

	}
}

/**
* checks across all project category trees in {list_category}
*
* some quickly hacked checks ..
* sure  more elegant and performat solutions exists.
*/
class CategoriesNestedSetDBChecks
{

	/** check if all category tree sets (1 tree set per project + 1 global) are in an ok state (no crossing node lft-rgt)
	* example:
	*  1------------------------------------18
	*    2-3  4-----9 10-11 12------------17
	*           5---8          13-14 15-16
	*            6-7
	*
	* example of a bad state:
	* node1 4------9
	* node2  5------10
	*/
	static function checkOverlapped(&$db, &$page)
	{
		$treeerrors = $db->query("SELECT c1.project_id, COUNT(*) AS count
			FROM {list_category} c1
			JOIN {list_category} c2 ON c1.project_id=c2.project_id
			WHERE c1.lft<c2.lft
			AND c1.rgt>c2.lft
			AND c1.rgt<c2.rgt
			GROUP BY c1.project_id");
		if ($db->countRows($treeerrors)) {
			$treeerrors=$db->fetchAllArray($treeerrors);
			$page->assign('cattreeerrors', $treeerrors);
		}

	}

	/**
	*  a state that should never happen in a nested set model.
	*  example: lft=3 rgt=2 should never happen
	*/
	static function checkFlipped(&$db, &$page)
	{
		$rgtbelowequallft = $db->query("SELECT COUNT(*) FROM {list_category} WHERE rgt <= lft");
		$rgtbelowequallft = $db->fetchOne($rgtbelowequallft);
		if ($rgtbelowequallft > 0) {
			$page->assign('cattreelftrgt', $rgtbelowequallft);
		}
	}

	// Check: in a nested set model there must lft and rgt number together be unique for each tree.
	static function checkLftRgtUnique(&$db, &$page)
	{
		$cattreenonunique = $db->query("SELECT project_id, lft, COUNT(*) c
			FROM (
				SELECT project_id, category_id, lft FROM {list_category}
				UNION
				SELECT project_id, category_id, rgt AS lft FROM {list_category}
			) AS t
			GROUP BY project_id, lft
			HAVING COUNT(*)>1
			ORDER BY project_id, lft");
		if ($db->countRows($cattreenonunique)) {
			$cattreenonunique = $db->fetchAllArray($cattreenonunique);
			$page->assign('cattreenonunique', $cattreenonunique);
		}
	}


	/**
	* Checks if task of project A has category that belongs to project B
	*
	* This can happen if someone moves a task with a project category_id to another project while
	* not changing the category_id of the task to a global category_id or target project category_id.
	*
	* Might be tolerable or not depends on your use case.
	*
	*/
	static function checkTasks(&$db, &$page)
	{
		/** check if tasks have wrong category id, eg. after moving task to other project without changing to a global category or target project category.
		 * Or if a category was deleted while having tasks related to it.
		 * This may happen because older Flyspray version didn't warn while moving or user just overruled it, forcing the move to other project
		 * or just deleting a category. May be tolerable for old closed task for example, depends if you care about that.
		 * At least there is now a query that tells you about that.
		 */
		$wrongtaskcatscount = $db->query("
			SELECT COUNT(*)
			FROM {tasks} t
			LEFT JOIN {list_category} c ON t.product_category=c.category_id
			WHERE (t.project_id <> c.project_id AND c.project_id <>0)
			OR c.project_id IS NULL");
		$wrongtaskcatscount = $db->fetchOne($wrongtaskcatscount);
		$page->assign('wrongtaskcategoriescount', $wrongtaskcatscount);

		$wrongtaskcats = $db->query("
			SELECT t.task_id, t.product_category, t.project_id AS tpid, c.project_id AS cpid, t.is_closed
			FROM {tasks} t
			LEFT JOIN {list_category} c ON t.product_category=c.category_id
			WHERE (t.project_id <> c.project_id AND c.project_id <>0)
			OR c.project_id IS NULL
			ORDER BY t.project_id, t.is_closed, t.task_id desc
			LIMIT 20");
		$page->assign('wrongtaskcategories', $db->fetchAllArray($wrongtaskcats));
	}


	/**
	* builds html for drawing alls graphs of table {list_category}
	*/
	static function drawGraphs(&$db, &$page)
	{

		# second try, slower sql query, but contains depth of subtree for painting height of a node containing subnodes
		# TODO: look for optimization of query. Benefit of additional index like (project_id,lft)?
		# DO some perf graphing with 10,100,1000,10000,100000 nodes in multiple demo projects.
		# no issue for hundred of categories

		$cattreehtml='';
		$cattreeshtml=array();
		$lastprojectid=0;

		$sqlmaxdepth=$db->query("
			SELECT catlevels.project_id, MAX(level) AS level, MAX(rgt) AS rgt FROM
				(SELECT c1.project_id, c1.category_id,c1.category_name,c1.lft,c1.rgt, COUNT(c2.category_id) AS level
				FROM {list_category} c1
				LEFT JOIN {list_category} c2 ON c2.project_id=c1.project_id AND c1.lft>c2.lft AND c1.rgt<c2.rgt
				GROUP BY c1.category_id) catlevels
			GROUP BY catlevels.project_id
			ORDER BY catlevels.project_id ASC");
		$level=array();
		$maxrgt=array();
		$allmaxlevel=0;
		$heightfactor=10;
		$gapfactor=2;
		while ($maxlevels = $db->fetchRow($sqlmaxdepth)) {
			$level[$maxlevels['project_id']] = $maxlevels['level'];
			$maxrgt[$maxlevels['project_id']] = $maxlevels['rgt'];
			if ($maxlevels['level'] > $allmaxlevel) {
				$allmaxlevel=$maxlevels['level'];
			}
		}

		/** used abbreviations for the self joins:
		* c = category
		* a = anchestors
		* d = descendants
		* da = anchestors of descendants
		*/
		$sqlcattrees=$db->query("
			SELECT cat.project_id, cat.category_id, cat.category_name, cat.lft, cat.rgt, cat.level,
			COALESCE(MAX(descendants.level), cat.level) AS depth,
			(1+COALESCE(MAX(descendants.level), cat.level)-cat.level) AS height
			FROM (
				SELECT c.project_id, c.category_id, c.category_name, c.lft, c.rgt, COUNT(a.category_id) level
				FROM {list_category} c
				LEFT JOIN {list_category} a ON c.project_id=a.project_id AND a.lft<c.lft AND a.rgt>c.lft
				GROUP BY c.category_id
			) cat
			LEFT JOIN (
				SELECT c.project_id, c.category_id, c.lft, c.rgt, COUNT(da.category_id) AS level
				FROM {list_category} c
				LEFT JOIN {list_category} da ON c.project_id=da.project_id AND da.lft<c.lft AND da.rgt>c.rgt
				GROUP BY c.category_id
			) descendants ON descendants.project_id=cat.project_id AND descendants.lft>cat.lft AND descendants.rgt<cat.rgt
			GROUP BY cat.project_id, cat.category_id, cat.category_name, cat.lft, cat.rgt, cat.level
			ORDER BY cat.project_id, cat.lft");

		while ($t = $db->fetchRow($sqlcattrees)) {
			if ($lastprojectid != $t['project_id']) {

				$levelprcss[$lastprojectid]='';
				for ($i=0; $i <= $level[$lastprojectid]; ++$i) {
					$levelprcss[$lastprojectid] .= '.cattree.p'.$lastprojectid
					.' .l'.$i.' {height:'. (($level[$lastprojectid]-$i+1)*($heightfactor + 2*$gapfactor))
					.'px;top:'.($i*$heightfactor). "px}\n";
				}
				$levelprcss[$lastprojectid].='div.cattree.p'.$lastprojectid.' {min-height:'.(20+$level[$lastprojectid]*($heightfactor+ 2*$gapfactor)).'px;min-width:'.($maxrgt[$lastprojectid]*$lastwidthfactor).'px;}';

				$cattreeshtml[$lastprojectid]=array(
					'html'=>$cattreehtml,
					'project_id'=>$lastprojectid,
					'css'=>$levelprcss[$lastprojectid]
				);
				$cattreehtml='';

			}
			if ($maxrgt[$t['project_id']]<100){
				$wfactor=10;
			} else if ($maxrgt[$t['project_id']]<500){
				$wfactor=4;
			} else {
				$wfactor=2;
			}
			if ($t['height']<1) {
				$t['height']=1;
			}
			$cattreehtml.='<i class="l'. ($t['level'])
				.(($t['rgt']-$t['lft']==1)?' leaf':'')
				.'"'
				.' style="left:'. ($t['lft']*$wfactor) . 'px;'
				.'height:'.(($heightfactor + 2*$gapfactor)*$t['height']) .'px;'
				.'width:'. (($t['rgt']-$t['lft'])>0?(($t['rgt']-$t['lft'])*$wfactor):5). 'px"'
				.' data-lft="'.$t['lft'].'"'
				.' data-rgt="'.$t['rgt'].'"'
				.' title="'.Filters::noXSS($t['category_name']).'"></i>';
			$lastprojectid=$t['project_id'];
			$lastwidthfactor=$wfactor;
		}

		$levelprcss[$lastprojectid]='';
		for ($i=0; $i <= $level[$lastprojectid]; ++$i) {
			$levelprcss[$lastprojectid] .= '.cattree.p'.$lastprojectid
			.' .l'.$i.' {height:'. (($level[$lastprojectid]-$i+1)*($heightfactor + 2*$gapfactor))
			.'px;top:'.($i*$heightfactor). "px}\n";
		}
		$levelprcss[$lastprojectid].='div.cattree.p'.$lastprojectid.' {min-height:'.(20+$level[$lastprojectid]*($heightfactor+ 2*$gapfactor)).'px;min-width:'.($maxrgt[$lastprojectid]*$lastwidthfactor).'px;}';

		$cattreeshtml[$lastprojectid]=array(
			'html'=>$cattreehtml,
			'project_id'=>$lastprojectid,
			'css'=>$levelprcss[$lastprojectid]
		);


/*
		# first try, faster sql query, but without subtree depth info for drawing, not as elegant
		$sqlcattrees=$db->query("SELECT category_id,project_id,lft,rgt,show_in_list,category_name
			FROM {list_category}
			ORDER BY project_id ASC, lft ASC");

		$clevel=0;
		$maxlevel=0;
		$allmaxlevel=0;
		$cattreehtml='';
		$cattreeshtml=array();
		$parentlft=array();
		$parentlft[0]=0;
		$parentrgt=array();
		$parentrgt[0]=-1; # for first root category compare with noenexistent parent
		$lastprojectid=0;
		$catprid=0;
		$levelprcss=array();
		$lastlft=0;
		$lastrgt=0;
		$heightfactor=10;
		$gapfactor=2;
		$lastprojectid=0;
		$maxrgt=0;
		while ($t = $db->fetchRow($sqlcattrees)) {
			# we start a new category tree
			if ($lastprojectid != $t['project_id']) {
				# reset
				$clevel=0;
				$lastlft=$t['lft'];
				$lastrgt=$t['rgt'];
				$parentlft=array();
				$parentlft[0]=0;
				$parentrgt=array();
				$parentrgt[0]=-1;
				if ($maxlevel < 20) {
					$heightfactor=10;
					$gapfactor=2;
				} else {
					$heightfactor=5;
					$gapfactor=1;
				}

				$levelprcss[$lastprojectid]='';
				for ($i=0; $i <= $maxlevel; ++$i) {
					$levelprcss[$lastprojectid] .= '.cattree.p'.$lastprojectid.' .l'.$i.' {height:'. (($maxlevel-$i+1)*($heightfactor + 2*$gapfactor)) . 'px;top:'.($i*$heightfactor). "px}\n";
				}
				$levelprcss[$lastprojectid].='div.cattree.p'.$lastprojectid.' {min-height:'.(20+$maxlevel*($heightfactor+ 2*$gapfactor)).'px;min-width:'.($maxrgt*10).'px;}';

				$cattreeshtml[$lastprojectid]=array(
					'html'=>$cattreehtml,
					'project_id'=>$lastprojectid,
					'css'=>$levelprcss[$lastprojectid]
				);
				$maxlevel=0;
				$maxrgt=1;
				$cattreehtml='';
			}

			if ($t['rgt'] > $maxrgt) {
				$maxrgt=$t['rgt'];
			}

			#$err='<pre>'.print_r($t,true); break;
			if ($t['lft'] != 1 && $lastlft < $t['lft'] && $lastrgt > $t['rgt']) {
				# nest it in
				$clevel++;
				$parentlft[$clevel] = $lastlft;
				$parentrgt[$clevel] = $lastrgt;
				if ($clevel>$maxlevel) {
					$maxlevel++;
					if($maxlevel>$allmaxlevel){
						$allmaxlevel=$maxlevel;
					}
				}
			} elseif ($parentrgt[$clevel] == ($t['lft']-1)) {
				# go level back, new subtree
				$clevel--;
			} elseif ( ($clevel-1)>0 && $parentrgt[$clevel-1] == ($t['lft']-1)) {
				# go 2 levels back, new subtree
				$clevel=$clevel-2;
			} elseif ( ($clevel-2)>0 && $parentrgt[$clevel-2] == ($t['lft']-1)) {
				# go 2 levels back, new subtree
				$clevel=$clevel-3;
			} elseif ( ($clevel-3)>0 && $parentrgt[$clevel-3] == ($t['lft']-1)) {
				# go level back, new subtree
				$clevel=$clevel-4;
			} elseif ( ($clevel-4)>0 && $parentrgt[$clevel-4] == ($t['lft']-1)) {
				# go level back, new subtree
				$clevel=$clevel-5;
			} elseif ( ($clevel-5)>0 && $parentrgt[$clevel-5] == ($t['lft']-1)) {
				# go level back, new subtree
				$clevel=$clevel-6;
			} else {
				# leave on same level in same subtree
			}

			$cattreehtml.='<i class="l'. $clevel .'"'
				.' style="left:'. ($t['lft']*10) . 'px;'
				.'width:'. (($t['rgt']-$t['lft'])>0?(($t['rgt']-$t['lft'])*10):5). 'px"'
				.' data-lft="'.$t['lft'].'"'
				.' data-rgt="'.$t['rgt'].'"'
				.' title="'.Filters::noXSS($t['category_name']).'"></i>';
			$lastlft = $t['lft'];
			$lastrgt = $t['rgt'];
			$lastprojectid=$t['project_id'];
		}


		// add the output of last project category tree
		if ($maxlevel < 20) {
			$heightfactor=10;
		} else {
			$heightfactor=5;
		}
		$levelprcss[$lastprojectid]='';
		for ($i=0; $i <= $maxlevel; $i++) {
			$levelprcss[$lastprojectid] .= '.cattree.p' . $lastprojectid .' .l' . $i . '{height:'. (($maxlevel-$i+1)*($heightfactor + 2*$gapfactor)).'px;top:'.($i*$heightfactor). "px}\n";
		}
		$levelprcss[$lastprojectid].='div.cattree.p'.$lastprojectid.' {min-height:'.(20+$maxlevel*($heightfactor+ 2*$gapfactor)).'px;min-width:'.($maxrgt*10).'px;}';

		$cattreeshtml[$lastprojectid]=array(
			'html'=>$cattreehtml,
			'project_id'=>$lastprojectid,
			'css'=>$levelprcss[$lastprojectid]
		);
		// end last project category tree

*/
		$levelallcss = '';
		$levelcolors = array(
			'rgba(120,120,0,0.1)',
			'rgba(160,80,0,0.1)',
			'rgba(240,0,0,0.1)',
			'rgba(160,0,80,0.1)',
			'rgba(80,0,160,0.1)',
			'rgba(0,0,240,0.1)',
			'rgba(0,80,160,0.1)',
			'rgba(0,160,80,0.1)',
			'rgba(0,240,0,0.1)',
			'rgba(80,160,0,0.1)',
		);

		for ($i=0; $i <= $allmaxlevel; $i++) {
			$levelallcss.='.cattree .l' . $i . ' {background-color:' . (isset($levelcolors[$i]) ? $levelcolors[$i]:"rgba(80,80,80,0.1)") . "}\n";
		}

		$page->assign('categorytrees', $cattreeshtml);
		$page->assign('levelallcss', $levelallcss);

	} //end function

} // end class


/**
* This can do some checks on a (submitted) full tree array
*/
class CategoriesNestedSetArrayChecks
{

}
