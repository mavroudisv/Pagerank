<?php
/*
 *  PageRank implementation in php & js.
 * 
 * Vasilis Mavroudis Dec 2011 - Jan 2012
 * http://mavroudisv.eu
 * 
 * Graphical representation of nodes and edges powered by Dracula Graph Library
 * http://www.graphdracula.net/
 *
 */



function prcalc($nodes_ar, $damping = 0.85) {
        $pagerank = array();
        $temppr = array();
        $nodeCount = count($nodes_ar);

        //Set pagerank for each node as 1/N
        $initialpr = 1/$nodeCount; 
        foreach($nodes_ar as $node => $outbound) {
                $pagerank[$node] = $initialpr;

                //temppr used to save temporary pagerank
                $temppr[$node] = 0;
        }

        $change = 1;
        for ($i = 0; $i <100; $i++) {
				if ($change <= 0.00005) {break;}
                $change = 0;

                //Pagerank of each node
                foreach($nodes_ar as $node => $outbound) {
                        $outboundCount = count($outbound);
                      $distrpr = $pagerank[$node] / $outboundCount;

                        foreach($outbound as $link) {
                                $temppr[$link] += $distrpr;
                        }
                }
               
                $total = 0;
                // calculate the new pagerank
                foreach($nodes_ar as $node => $outbound) {
                        $temppr[$node]  = ((1 - $damping) / $nodeCount) + $damping * $temppr[$node];
                        $change += abs($pagerank[$node] - $temppr[$node]);
                        $pagerank[$node] = $temppr[$node];
                        $temppr[$node] = 0;
                        $total += $pagerank[$node];
                }

                // Normalise pageranks
                foreach($pagerank as $node => $score) {
                        $pagerank[$node] /= $total;
                }
        }
		
        return $pagerank;
          
}

//if not submit back to form
if (!isset($_POST['submit'])) {
 
 header('Location: /pagerank/index.html');
 
}else if (isset($_POST['submit'])) {
	
echo '<html>
<head>
    <script type="text/javascript" src="js/raphael-min.js"></script>
    <script type="text/javascript" src="js/dracula_graffle.js"></script>
    <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="js/dracula_graph.js"></script>
    <script type="text/javascript" src="js/dracula_algorithms.js"></script>
	<script language="javascript"> 
	function toggle() {
	var ele = document.getElementById("toggleText");
	var text = document.getElementById("displayText");
	if(ele.style.display == "block") {
    		ele.style.display = "none";
		text.innerHTML = "Show";
  	}
	else {
		ele.style.display = "block";
		text.innerHTML = "Hide";
	}
} 
</script>';
	
	
	
	
$lines='0';
$nodes_ar=$_POST["nodes"];
$damping=$_POST["damping"];
$list=$_POST["list"];
$graph=$_POST["graph"];

//input sanitization
//nodes
filter_var($nodes_ar, FILTER_SANITIZE_MAGIC_QUOTES);
$nodes_ar=preg_replace('/[^0-9,\n]/', '', $nodes_ar);
$nodes_ar=nl2br($nodes_ar);
//echo $nodes_ar;

//damping
//filter_var($damping, FILTER_SANITIZE_MAGIC_QUOTES);
//$damping=preg_replace('/[^0-9.]/', '', $damping);

//list
filter_var($list, FILTER_SANITIZE_MAGIC_QUOTES);
$list=preg_replace('/[^1]/', '', $list);

//graph
filter_var($graph, FILTER_SANITIZE_MAGIC_QUOTES);
$graph=preg_replace('/[^1]/', '', $graph);


//read input into 2d array
$node_links = array();
foreach (explode('<br />', $nodes_ar) as $piece) {
	$lines++;
    $node_links[] = explode(',', $piece);
}

//check if node numbers are valid
//echo 'There are '.$lines.' lines.<p>';
for ($i = 0; $i <$lines; $i++) {
	for ($j = 0; $j <$lines; $j++) {
		if ($node_links[$i][$j]!=null) {
			$node_links[$i][$j]= ereg_replace("[^0-9]", "", $node_links[$i][$j] );
			if ($node_links[$i][$j]>=$lines){echo '<p>Warning: Node '. $node_links[$i][$j] .' is used as outbound without being defined!<p>';}
		}
	}
}

//check if damping factor input is a number
//if (preg_match ('/[^0-9.]/', $damping) | $damping==null){$damping='0.85'; }

//call pagerank function (prcalc)
$pagerank= prcalc($node_links,$damping);

//print graph is selected
if ($graph=='1'){
	echo '<script type="text/javascript">	
		$(document).ready(function() {
		var width = $(document).width();
		var height = $(document).height();
		var g = new Graph();
		g.edgeFactory.template.style.directed = true;';
		
	//Add colored nodes, based on pagerank (7 types of nodes)
	for ($i = 0; $i<$lines; $i++) {
		$pagerank_round[$i]=round($pagerank[$i],3);
		if ($pagerank[$i]<0.02){
			echo 'g.addNode("'.$i.'\n('.$pagerank_round[$i].')",{color: "#FFFF00", height: "20", width: "20"});';
		}else if ($pagerank[$i]>=0.02 && $pagerank[$i]<0.04){
			echo 'g.addNode("'.$i.'\n('.$pagerank_round[$i].')",{color: "#FFCC00", height: "27", width: "25"});';
		}else if ($pagerank[$i]>=0.04 && $pagerank[$i]<0.06){
			echo 'g.addNode("'.$i.'\n('.$pagerank_round[$i].')",{color: "#ff9900", height: "32", width: "30"});';
		}else if ($pagerank[$i]>=0.06 && $pagerank[$i]<0.08){
			echo 'g.addNode("'.$i.'\n('.$pagerank_round[$i].')",{color: "#ff6600", height: "36", width: "35"});';
		}else if ($pagerank[$i]>=0.08 && $pagerank[$i]<0.1){
			echo 'g.addNode("'.$i.'\n('.$pagerank_round[$i].')",{color: "#FF3300", height: "42", width: "40"});';
		}else if ($pagerank[$i]>=0.1 && $pagerank[$i]<0.12){
			echo 'g.addNode("'.$i.'\n('.$pagerank_round[$i].')",{color: "#FF0000", height: "49", width: "45"});';
		}else if ($pagerank[$i]>=0.12){
			echo 'g.addNode("'.$i.'\n('.$pagerank_round[$i].')",{color: "#990000", height: "55", width: "55"});';
		}
	}


	for ($i = 0; $i<$lines; $i++) {
		for ($j = 0; $j<$lines; $j++) {
			if ($node_links[$i][$j]!=null) {
				echo 'g.addEdge("'.$i.'\n('.$pagerank_round[$i].')", "'.$node_links[$i][$j].'\n('.$pagerank_round[$node_links[$i][$j]].')");';
			}
		}
	}


	echo 'var layouter = new Graph.Layout.Spring(g);
	layouter.layout();
		 
	var renderer = new Graph.Renderer.Raphael(\'canvas\', g, width-20, height-20);
	renderer.draw();
	});	
	</script>
	<style type="text/css">
		body {
		overflow: hidden;
		}
	</style>';
 }

//print list if selected
if ($list=='1'){
echo '<a id="displayText" style="position:absolute;top:3;right:20;font:14px/20px Georgia, Garamond, Serif;text-decoration:none" href="javascript:toggle();">Show</a>';
echo '<div id="toggleText" style="height:250px;width:200px;display: none; background-color:#FFFFFF; font:16px/26px Georgia, Garamond, Serif;overflow:auto; border:1px solid; padding:2px;position:absolute;top:23;right:3;border-radius: 10px;">';
	for ($i = 0; $i<$lines; $i++) {
		$sum+=$pagerank[$i];
		echo '<b>'.$i.'</b>: '.$pagerank[$i].'<br />';
	}
	echo '<b>The PR sum is: '.$sum.'</b>';
echo '</div>
<p style="font-size:10px;"></p>';


}

echo '</head>
<body>
<div id="canvas"></div>
</body>
</html>';

}

?>
