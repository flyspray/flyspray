/** coding:utf-8 */

/** Some helper's functions
 * Public domain or unknown author(s)
 * TODO : move it to another file
 **/
function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}

function getParent(element, parentTagName)
{
	if ( ! element )
		return null;
	else if ( element.nodeType == 1 && element.tagName.toLowerCase() == parentTagName.toLowerCase() )
		return element;
	else
		return getParent(element.parentNode, parentTagName);
}
function getNextSibling(elt)
{
	var sibling = elt.nextSibling;
	while (sibling != null) {
		if (sibling.nodeName == elt.nodeName) return sibling;
		sibling = sibling.nextSibling;
	}
	return null;
}
function getPreviousSibling(elt)
{
	var sibling = elt.previousSibling;
	while (sibling != null) {
		if (sibling.nodeName == elt.nodeName)
			return sibling;
		sibling = sibling.previousSibling;
	}
	return null;
}
function strRepeat(str, n) {
	var i, ret = '';
	for ( i = 0; i < n; i++ )
		ret += str;
	return ret;
}

/** @class TableControl
 * @author Rémi Lanvin
 */
var TableControl = {
	controlled_tables: [],
	active_rows: [],
	default_options: {
		tree:              false,    // way of sorting rows
		activeClassName:   'active',
		spreadActiveClass: true,     // spread the 'active' class to childrens ?
		controlBox:        null      // an id
	},
	
	/**
	 * Init the table 'table_id'
	 * @param table_id table id
	 * @param options  an 'options' object - optional
	 */
	create: function(table_id)
	{

		var table = $(table_id);
		if ( ! table || table.tagName != 'TABLE' )
			return;

		var options = Object.extend(this.default_options, arguments[1] || {});
		
		var tbody = table.getElementsByTagName('TBODY');
		var row = tbody[0].getElementsByTagName('TR');
	
		var i, j, input;
		for (i = 0; i < row.length; i++ ) {
			row[i].table_id = table_id;

			input = row[i].getElementsByTagName("INPUT");
			// initialize tree values
			if ( options.tree ) {
				row[i].tree_left = 0;
				row[i].tree_right = 0;

				for ( j = 0; j < input.length; j++ ) {
					if ( input[j].name.substring(0,3) == 'lft' )
						row[i].tree_left = input[j];
					else if ( input[j].name.substring(0,3) == 'rgt' )
						row[i].tree_right = input[j];
				}

				row[i].tree_diff = parseInt(row[i].tree_right.value,10) - parseInt(row[i].tree_left.value,10);

				span = row[i].getElementsByTagName("SPAN");
				for ( j = 0; j < span.length; j++ ) {
					if ( span[j].className == "depthmark" )
						row[i].tree_depthmark = span[j];
				}
			}
			// initialize classical position values
			else {
				for ( j = 0; j < input.length; j++ ) {
					if ( input[j].name.substring(0,13) == 'list_position' )
						row[i].list_position = input[j];
				}
			}
			Event.observe(row[i], 'click', function(e) { TableControl._onClick(e) }, false);
		}
		if ( options.controlBox ) {
			options.controlBoxElt = $(options.controlBox);
			// may not be a control box if there are no rows currently
			if (options.controlBoxElt)
			   options.controlBoxElt.style.left = parseInt(findPos(table)) + parseInt (table.scrollWidth - 30) + 'px';
		}

		
		if ( options.tree ) {
			this._buildTree(table);
			var form = getParent(table, "FORM");
			if ( form )
				Event.observe(form, 'submit', function(e) { TableControl._calculateValues(table.root_node) }, false);
		}
		
		this.controlled_tables[table_id] = options;
	},
	
	/**
	 * Move up the active row
	 */
	up: function(table_id)
	{
		var options;
		if ( ! ( options = this.controlled_tables[table_id] ) )
			return;

		var row = this.active_rows[table_id];
		if ( ! row )
			return;

		if ( options.tree ) {
			var previous_row = this._getPreviousTreeNode(row);
			// already on the top
			if ( ! previous_row ) return;

			var parent = row.parentNode;
			this._swapTreeNode(row, previous_row);
			this._moveSubTreeBefore(row, previous_row, parent);
		}
		else {
			var previous_row = getPreviousSibling(row);
			if ( ! previous_row )
				return;
			var parent = row.parentNode;
			parent.removeChild(row);
			parent.insertBefore(row, previous_row);

			var tmp = row.list_position.value;
			row.list_position.value = previous_row.list_position.value;
			previous_row.list_position.value = tmp;
		}
	},
	
	/**
	 * Move down the active row
	 */
	down: function(table_id)
	{
		var options;
		if ( ! ( options = this.controlled_tables[table_id]) )
			return;

		var row = this.active_rows[table_id];
		if ( ! row )
			return;

		if ( options.tree ) {
			var next_row = this._getNextTreeNode(row);
			// already on the bottom
			if ( ! next_row ) return;
			
			var next_next_row = this._getNextTreeNode(next_row);
			var parent = row.parentNode;
			
			this._swapTreeNode(row, next_row);
			
			if ( next_next_row )
				this._moveSubTreeBefore(row, next_next_row, parent);
			else {
				var last_child = ( next_row.childs.length > 0 ? next_row.childs[next_row.childs.length - 1] : next_row );
				var end_row = getNextSibling(last_child);
				this._moveSubTreeBefore(row, end_row, parent);
			}
		}
		else {
			var next_row = getNextSibling(row);
			if ( ! next_row )
				return;
			var parent = next_row.parentNode;
			parent.removeChild(next_row);
			parent.insertBefore(next_row, row);
			
			var tmp = row.list_position.value;
			row.list_position.value = next_row.list_position.value;
			next_row.list_position.value = tmp;
		}
	},
	
	/**
	 * Move deeper the active row
	 * Only works when the option 'tree' is true
	 */
	deeper: function(table_id)
	{
		var options;
		if ( ! ( options = this.controlled_tables[table_id] ) )
			return;
		if ( ! options.tree )
			return;

		var row = this.active_rows[table_id];
		if ( ! row )
			return;

		// check if futur parent is deeper enough
		var previous_row = getPreviousSibling(row);
		if ( ! previous_row || (this._getDepth(previous_row) < this._getDepth(row) ))
			return;

		previous_row = this._getPreviousTreeNode(row);
		this._removeTreeNode(row);
		this._addTreeNode(row, previous_row);

		this._forEachChilds(row, function(node) {
			var old_depth = TableControl._getDepth(node);
			var new_depth = old_depth + 1;
			var e = new RegExp('depth' + old_depth, "g");
			node.className = node.className.replace(e, 'depth' + new_depth);
			node.tree_depthmark.innerHTML = strRepeat('&rarr;', new_depth);
		});
	},

	/**
	 * Move shallower the active row
	 * Only works when the option 'tree' is true
	 */
	shallower: function (table_id)
	{
		var options;
		if ( ! ( options = this.controlled_tables[table_id] ) )
			return;
		if ( ! options.tree )
			return;

		var row = this.active_rows[table_id];
		if ( ! row )
			return;
		
		// check the depth
		if ( this._getDepth(row) <= 0 )
			return;

		var parent = row.parent;
		this._removeTreeNode(row);
		this._addTreeNode(row, parent.parent, parent);
		
		this._forEachChilds(row, function(node) {
			var old_depth = TableControl._getDepth(node);
			var new_depth = old_depth - 1;
			var e = new RegExp('depth' + old_depth, "g");
			node.className = node.className.replace(e, 'depth' + new_depth);
			node.tree_depthmark.innerHTML = strRepeat('&rarr;', new_depth);
		});
	},
	
	/** @internal stuff **/

	/** @private
	 * OnClick handler. Activate the selected row
	 */
	_onClick: function(e)
	{
		var row = Event.element(e);
		if ( row.tagName != 'TR' )
			row = getParent(row, 'TR');

		var active_row = this.active_rows[row.table_id];
		var options = this.controlled_tables[row.table_id];

		// desactivate previously active row
		if ( active_row ) {
			Element.removeClassName(active_row, options.activeClassName);
			if ( options.tree && options.spreadActiveClass ) {
				this._forEachChilds(active_row, function(e) {
					Element.removeClassName(e, options.activeClassName);
				});
			}
		}
		else if ( options.controlBoxElt ) {
			Element.addClassName(options.controlBoxElt, options.activeClassName);
		}
		// if clicking on the same row : no more active row
		if ( active_row && active_row == row ) {
			this.active_rows[row.table_id] = null;
			Element.removeClassName(options.controlBoxElt, options.activeClassName);
		}
		// else activate the new selected row
		else {
			Element.addClassName(row, options.activeClassName);
			if ( options.tree && options.spreadActiveClass ) {
				this._forEachChilds(row, function(e) {
					Element.addClassName(e, options.activeClassName);
				});
			}
			this.active_rows[row.table_id] = row;
		}
		return;
	},

	/** @private
	 * Convert a class name like "depthN" to "N"
	 */
	_getDepth: function(elt)
	{
		return ( elt ? parseInt(elt.className.substr(5),10) : -1 );
	},

	/** @private
	 * Return the previous node in the tree
	 */
	_getPreviousTreeNode: function(node)
	{
		if ( ! node || ! node.parent ) return null;
		var i = node.parent.childs.indexOf(node);
		return (i > 0 ? node.parent.childs[i-1] : null);
	},

	/** @private
	 * Return the next node in the tree
	 */
	_getNextTreeNode: function (node)
	{
		if ( ! node || ! node.parent ) return null;
		var i = node.parent.childs.indexOf(node);
		return (i < node.parent.childs.length - 1 ? node.parent.childs[i+1] : null);
	},

	/** @private
	 * Swap 'node1' and 'node2' in the tree
	 */
	_swapTreeNode: function(node1, node2)
	{
		if ( !node1 || !node2 || (node1.parent != node2.parent) )
			return false;

		var node1_pos = node1.parent.childs.indexOf(node1);
		var node2_pos = node1.parent.childs.indexOf(node2);
		node1.parent.childs[node1_pos] = node2;
		node1.parent.childs[node2_pos] = node1;
		return true;
	},

	/** @private
	 * Remove 'nove' from the tree
	 */
	_removeTreeNode: function(node)
	{
		if ( ! node || ! node.parent )
			return false;
		var i = node.parent.childs.indexOf(node);
		node.parent.childs.splice(i, 1);
		node.parent = null;
	},

	/** @private
	 * Add 'node' as child of 'parent'.
	 * If 'position' if given, 'node' is added before 'position'. Else it is
	 * added after the last child.
	 */
	_addTreeNode: function(node, parent, position)
	{
		if ( ! node || ! parent )
			return;
		node.parent = parent;
		if ( position ) {
			var i = parent.childs.indexOf(position) + 1;
			if ( i < parent.childs.length )
				parent.childs.splice(i, 0, node);
			else
				parent.childs.push(node);
		}
		else
			parent.childs.push(node);
	},

	/** @private
	 * Do f(node) for each node's childs (including itself).
	 */
	_forEachChilds: function(node, f)
	{
		f(node);
		var i;
		for (i = 0; i < node.childs.length; i++ )
			this._forEachChilds(node.childs[i], f);
	},
	/** @private
	 * Move the subtree starting from 'node' before 'position'
	 * @param parent the DOM parent
	 */
	_moveSubTreeBefore: function(node, position, parent)
	{
		this._forEachChilds(node, function(node) { 
			parent.removeChild(node);
			if ( position )
				parent.insertBefore(node, position);
			else
				parent.appendChild(node);
		});
	},
	/** @private
	 * Rebuild a tree from a non-recursive DOM structure
	 */
	_buildTree: function(table)
	{
		if ( ! table )
			return;

		table.root_node = {
			is_root_node: true,
			childs: new Array()
		};
		var tbody = table.getElementsByTagName("TBODY");
		var row = tbody[0].getElementsByTagName("TR");
		var i;
		for ( i = 0; i < row.length; i++ ) {
			if ( this._getDepth(row[i]) == 0 ) {
				table.root_node.childs.push(row[i])
				this._buildSubTree(row[i], table.root_node);
			}
		}
	},
	_buildSubTree: function(node, parent)
	{
		node.parent = parent;
		var node_depth = this._getDepth(node);
		var child_depth = node_depth + 1;
		var next_node = getNextSibling(node);
		var next_node_depth = -1;
		node.childs = new Array();
		while ( next_node && ( (next_node_depth = this._getDepth(next_node)) > node_depth ) ) {
			if ( next_node_depth == child_depth ) {
				node.childs.push(next_node);
				this._buildSubTree(next_node, node);
			}
			next_node = getNextSibling(next_node);
		}
	},
	/** @private
	 * Do a tree traversal algorithm and fill "lft" and "rgt" values
	 */
	_calculateValues: function(node, n)
	{
		if ( node.is_root_node ) {
			n = new Array();
			n.push(1);
		}
		else {
			node.tree_left.value = ++(n[0]);
		}
		var i;
		for ( i = 0; i < node.childs.length; i++ )
			this._calculateValues(node.childs[i], n);
		
		if ( ! node.is_root_node ) {
			node.tree_right.value = ++(n[0]);
		}
	}
}
