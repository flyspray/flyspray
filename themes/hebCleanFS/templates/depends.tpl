<div style="text-align:center;" class="box">
  <p><b><?php echo Filters::noXSS(L('pruninglevel')); ?>: </b><?php echo implode(" &nbsp;|&nbsp; \n", $strlist); ?></p>
  <h2><a href="<?php echo Filters::noXSS(CreateUrl('details', $task_id)); ?>">FS#<?php echo $task_id; ?></a>: <?php echo Filters::noXSS(L('dependencygraph')); ?></h2>

<div id="infovis" style="width:90%;height:50em"></div>    

<script type="text/javascript">
  // init data
  var json = <?php echo $jasonData; ?>;  
   
   // init ForceDirected
  var fd = new $jit.ForceDirected({
    //id of the visualization container
    injectInto: 'infovis',
    //Enable zooming and panning
    //by scrolling and DnD
    Navigation: {
      enable: true,
      //Enable panning events only if we're dragging the empty
      //canvas (and not a node).
      zooming: 50 //zoom speed. higher is more sensible
    },
    // Change node and edge styles such as
    // color and width.
    // These properties are also set per node
    // with dollar prefixed data-properties in the
    // JSON structure.
    Node: {
      overridable: true,
    },
    Edge: {
      overridable: false,
      color: '#555555',
      type: 'arrow', 
      dim: 25,
      lineWidth: 1
    },
    //Native canvas text styling
    Label: {
      type: 'HTML', //Native or HTML
      size: 10,
      style: 'bold',
    },
    //Add Tips
    Tips: {
      enable: true,
      onShow: function(tip, node) {
        // Count connections
        var count = 0;                
        node.eachAdjacency(function(adj) {  
            count++;
        }); 

        // Display node info in tooltip
        tip.innerHTML = "<div class=\"popup\" style=\"width:200px\">" + node.name
          + "<div><b><?php echo Filters::noXSS(L('connectedtasks')); ?></b> " + count + "</div></div>";
      }
    },
    // Add node events
    Events: {
      enable: true,
      type: 'Native'
    },
    //Number of iterations for the FD algorithm
    iterations: 50,
    //Edge length
    levelDistance: 130,
    // Add text to the labels. This method is only triggered
    // on label creation and only for DOM labels (not native canvas ones).
    onCreateLabel: function(domElement, node){
      domElement.innerHTML = node.name;
      var style = domElement.style;
      style.fontSize = "1em";
      style.color = "#ddd";
    },
    // Change node styles when DOM labels are placed
    // or moved.
    onPlaceLabel: function(domElement, node){
      var style = domElement.style;
      var left = parseInt(style.left);
      var top = parseInt(style.top);
      var w = domElement.offsetWidth;
      style.left = (left - w / 2) + 'px';
      style.top = top + 'px';
      style.padding = 25 + 'px';
      style.display = '';
    }
  });
  // load JSON data.
  fd.loadJSON(json);
  // compute positions incrementally and animate.
  fd.computeIncremental({
    iter: 40,
    property: 'end',
    onComplete: function(){      
      fd.animate({
        modes: ['linear'],
        transition: $jit.Trans.Elastic.easeOut,
        duration: 1500
      });
    }
  });
  // end
  
</script>

</div>