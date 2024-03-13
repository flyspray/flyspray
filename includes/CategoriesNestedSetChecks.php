<?php

/**
* some quickly hacked checks ..
* sure  more elegant and performat solutions exists.
*/
class CategoriesNestedSetChecks
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
	*  example: lft3 rgt2 should never happen
	*/
	static function checkFlipped(&$db, &$page)
	{
		// another state that should never happen in a nested set model.
		$rgtbelowequallft = $db->query("SELECT COUNT(*) FROM {list_category} WHERE rgt <= lft");
		$rgtbelowequallft = $db->fetchOne($rgtbelowequallft);
		if ($rgtbelowequallft > 0) {
			$page->assign('cattreelftrgt', $rgtbelowequallft);
		}
	}

	// another check: in a nested set model there must lft and rgt number together be unique for a tree
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

	static function drawGraphs(&$db, &$page)
	{
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

		$cattreeshtml[$t['project_id']]=array(
			'html'=>$cattreehtml,
			'project_id'=>$lastprojectid,
			'css'=>$levelprcss[$lastprojectid]
		);
		// end last project category tree

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

		#die($err);
		for ($i=0; $i <= $allmaxlevel; $i++) {
			$levelallcss.='.cattree .l' . $i . ' {background-color:' . (isset($levelcolors[$i]) ? $levelcolors[$i]:"rgba(80,80,80,0.1)") . "}\n";
		}

		$page->assign('categorytrees', $cattreeshtml);
		$page->assign('levelallcss', $levelallcss);

	} //end function

} // end class
