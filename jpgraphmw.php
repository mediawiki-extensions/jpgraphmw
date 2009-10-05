<?php

/**
 * jpgraph.php
 * provide tags for drawing charts the easy way using jpgraph.
 * written by Yannig Perré
 * http://blog.simia.fr
 * To activate the functionality of this extension include the following in your
 * LocalSettings.php file:
 *
 * $jpgraphWikiDefaults = Array ( "size" => "200x120" );
 * $jpgraphLinesDefaults = Array ( "grid" => "xy", "min" => "0", "ylabel" => "4");
 * $jpgraphBarsDefaults = Array ( "grid" => "y", "min" => "0", "ylabel" => "4" );
 * $jpgraphPieDefaults = Array ( "3d" => "3d" );
 * require_once( "$IP/extensions/jpgraph.php" );
 *
 * Mostly inspirade by gchart4mw
 */

error_reporting (E_ALL);
$jpgraph_home = "$IP/extensions/jpgraph";

require_once("$jpgraph_home/src/jpgraph.php");
require_once("$jpgraph_home/src/jpgraph_line.php");
require_once("$jpgraph_home/src/jpgraph_date.php");
require_once("$jpgraph_home/src/jpgraph_pie.php");

if(! defined( 'MEDIAWIKI' ) ) {
  echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
  die( -1 );
} else {
  $wgExtensionCredits['parserhook'][] = array(
    'name' => 'jpgraph',
    'author' =>'Yannig Perré', 
    'url' => 'http://blog.simia.fr',
    'description' => 'this is an extension to use jpgraph in your wiki easily.'
    );
}

$wgExtensionFunctions[] = 'jpChartSetup';

// -----------------------------------------------------------------------------
function jpChartSetup() {
  global $wgParser;
  $wgParser->setHook( 'jplines', 'jpLinesRender' );
  $wgParser->setHook( 'jpline', 'jpLinesRender' );
  $wgParser->setHook( 'jpbars', 'jpBarsRender' );
  $wgParser->setHook( 'jpbar', 'jpBarsRender' );
  $wgParser->setHook( 'jppie', 'jpPieRender' );
}
          
// -----------------------------------------------------------------------------
function jpChartInit() {
  global $fieldsep;
  global $hasxlabel;
  global $hasylabel;
  global $scale;
  global $dateformat;
  global $haslegend;
  global $hasxgrid;
  global $hasygrid;
  global $ishorizontal;
  global $size;
  global $title;
  global $colors;
  global $fill;
  global $isstacked;
  global $is3d;
  global $min;
  global $max;
  global $ysteps;
  global $format;
  global $isantialias;
  global $rotatexlegend;
  global $rotateylegend;
  global $type;
  global $margin;

  $fieldsep = ",";
  $hasxlabel = false;
  $hasylabel = false;
  $scale = false;
  $dateformat = false;
  $haslegend = false;
  $hasxgrid = false;
  $hasygrid = false;
  $ishorizontal = false;
  $isantialias = true;
  $rotatexlegend = 0;
  $rotateylegend = 0;
  $size = "400x300";
  $margin = "60,20,50,80";
  $title = "";
  $colors = "#5555ff,#ff5555,#55ff55,#ff55ff,#A0F000,#ffff55,#956575,#55ffff,#ff00ff,#7f7f00,#A07fA0,#7f7f7f,#7f007f";
  $fill = "";
  $isstacked = false;
  $is3d = false;
  $min = 0;
  $max = false;
  $ysteps = 2;
  $format = "png";
  $type = "default";
}

// -----------------------------------------------------------------------------
function jpArgsDebug ( $args ) {
  $attr = array();    
  // make a list of attributes and their values and dump them, along with the user input
  foreach( $args as $name => $value )
    $attr[] = '<strong>' . htmlspecialchars( $name ) . '</strong> = ' . htmlspecialchars( $value );
  $rslt = implode( '<br />', $attr ) . "<br />";

  return $rslt;
}

// -----------------------------------------------------------------------------
function jpArgsParseCommon ( $args ) {
  if (is_null($args)) return;

  foreach( $args as $name => $value ) {
    if(preg_match("/^(no)?(size|rotatexlegend|rotateylegend|title|colors|nocolors|".
                  "margin|fill|nofill|dateformat|scale|format|fieldsep|max|min|ysteps)$/", $name, $field)) {
      global $$field[2];
      $$field[2] = ($field[1] == "no" ? "" : $value);
    } else if(preg_match("/^(no)?(legend|xlabel|ylabel)$/", $name, $field)) {
      $var = "has".$field[2];
      global $$var;
      $$var = ($field[1] != "no");
    } else if(preg_match("/^(no|not)?(horizontal|antialias|stacked|3d)$/", $name, $field)) {
      $var = "is".$field[2];
      global $$var;
      $$var = (!preg_match("/^(no|not)$/", $field[1]));
    } else switch ($name) {
      case "grid":
        switch ($value) {
          case "xy":
            $hasxgrid=true;
            $hasygrid=true;
            break;
          case "yx":
            $hasxgrid=true;
            $hasygrid=true;
            break;
          case "x":
            $hasxgrid=true;
            $hasygrid=false;
            break;
          case "y":
            $hasxgrid=false;
            $hasygrid=true;
            break;
        }
        break;
      case "nogrid":
        $hasxgrid=false;
        $hasygrid=false;
        break;
    }
  }
}

// -----------------------------------------------------------------------------
function jpApplySettings () {
  global $graph;
  global $type;
  global $hasxlabel;
  global $hasylabel;
  global $dateformat;
  global $haslegend;
  global $hasxgrid;
  global $hasygrid;
  global $ishorizontal;
  global $size;
  global $margin;
  global $title;
  global $colors;
  global $fill;
  global $isstacked;
  global $is3d;
  global $min;
  global $max;
  global $ysteps;
  global $color_list;
  global $isantialias;

  $color_list = split(",", $colors);

  list($size_x, $size_y) = split("x", $size);
  switch ($type) {
    case "pie":
      $graph = ($is3d ? new PieGraph3D($size_x, $size_y) : new PieGraph($size_x, $size_y));
      break;
    default:
      $graph = new Graph($size_x, $size_y);
      break;
  }
  if($title) {
    $graph->title->Set($title);
    $graph->title->SetFont(FF_DV_SANSSERIF, FS_BOLD, 12);
  }
  $graph->img->SetAntiAliasing($isantialias);
  if($margin) {
    list($lm, $rm, $tm, $bm) = split(",", $margin);
    $graph->SetMargin($lm, $rm, $tm, $bm);
  }
}

function jpLinesParse( $input, $parser ) {
  global $fieldsep;
  global $graph;
  global $color_list;
  global $scale;
  global $dateformat;
  global $min;
  global $max;
  global $isstacked;

  $datay = array();
  $datax = false;
  $labels = array();
  $plot_area_list = array();

  // retrieving data
  $i = 0;
  $max_row_count = -1;
  foreach(split("\n", $input) as $line) {
    // skip empty line or comments
    if(strlen($line) == 0 || preg_match("/^(\s*)#/", $line)) continue;
    $line_array = split($fieldsep, $line);
    // if first loop => setting label and continue with next loop
    if($i == 0) {
      $labels = $line_array;
      $i++; continue;
    }
    // Storing data
    for($j = 0; $j < count($line_array); $j++) {
      $datay[$j][] = $line_array[$j];
    }
    // check data integrity
    if($max_row_count == -1)
      $max_row_count = count($line_array);
    if($max_row_count != count($line_array)) {
      throw new Exception("Problem while parsing data");
    }
    $i++;
  }
  // if (x, y) curve => set datax with first set of datay
  if($scale) {
    $datax = $datay[0];
    $data_start = 1;
    if($scale == "xy") {
      $dateformat = "U";
    }
    for($i = 1; $i < count($datax); $i++) {
      if(!is_integer($datax[$i]))
        $datax[$i] = strtotime($datax[$i]);
    }
  } else {
    $data_start = 0;
  }

  // Creating data object
  for($i = $data_start; $i < count($datay); $i++) {
    $lineplot = new LinePlot($datay[$i], $datax);
    $lineplot->SetLegend($labels[$i]);
    $lineplot->mark->SetType(MARK_FILLEDCIRCLE);
    $lineplot->mark->SetFillColor($color_list[$i % count($color_list)]);
    if($isstacked) {
      $plot_area_list []= $lineplot;
      $lineplot->SetColor("gray");
      $lineplot->SetFillColor($color_list[$i % count($color_list)]);
    } else {
      $lineplot->SetColor($color_list[$i % count($color_list)]);
      $graph->Add($lineplot);
    }
  }
  if($isstacked) {
    $point_area = new AccLinePlot($plot_area_list);
    $graph->Add($point_area);
  }
}

// -----------------------------------------------------------------------------
function jpPostProcess () {
  global $graph;
  global $hasxlabel;
  global $hasylabel;
  global $scale;
  global $dateformat;
  global $haslegend;
  global $hasxgrid;
  global $hasygrid;
  global $ishorizontal;
  global $size;
  global $title;
  global $colors;
  global $fill;
  global $isstacked;
  global $is3d;
  global $min;
  global $max;
  global $ysteps;
  global $color_list;
  global $rotatexlegend;
  global $rotateylegend;

  $color_list = split(",", $colors);

  if($hasxgrid)
    $graph->xgrid->Show();
  if($hasygrid)
    $graph->ygrid->Show();
  if($scale) {
    $graph->SetScale("datlin");
  } else {
    $graph->SetScale("textlin");
  }
  $graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#BBCCFF@0.5');
  if($dateformat)
    $graph->xaxis->scale->SetDateFormat($dateformat);

  $graph->xaxis->SetFont(FF_DV_SANSSERIF);
  $graph->yaxis->SetFont(FF_DV_SANSSERIF);

  $graph->xaxis->SetLabelAngle($rotatexlegend);
  $graph->yaxis->SetLabelAngle($rotateylegend);
  $graph->yaxis->scale->SetAutoMin($min);
  $graph->yaxis->scale->SetAutoMax($max);
}

// -----------------------------------------------------------------------------
function jpFinalizeGraph( $input, $args ) {
  global $wgUploadDirectory;
  global $wgUploadPath;
  global $graph;
  global $format;
  global $title;

  // Generating image
  $img_name = md5(implode("", $args).$input).".$format";
  $graph->Stroke("$wgUploadDirectory/$img_name");
  return '<p><b><img src="'.$wgUploadPath."/".$img_name."\" alt=\"$title\"></b></p>";
}

// -----------------------------------------------------------------------------
function jpLinesRender( $input, $args, $parser ) {
  global $jpgraphWikiDefaults;
  global $jpgraphLinesDefaults;

  try {
    jpChartInit ();
    jpArgsParseCommon ($jpgraphWikiDefaults);
    jpArgsParseCommon ($jpgraphLinesDefaults);
    jpArgsParseCommon ($args);
    jpApplySettings ();

    jpLinesParse($input, $parser);
    jpPostProcess();

    return jpFinalizeGraph($input, $args);
  } catch(Exception $e) {
    return "<pre>".$e->getMessage()."\n".$e->getTraceAsString()."</pre>";
  }
}

function jpBarsRender( $input, $args, $parser ) {
  global $jpgraphWikiDefaults;
  global $jpgraphBarsDefaults;

  jpChartInit ();
  jpArgsParseCommon ($jpgraphWikiDefaults);
  jpArgsParseCommon ($jpgraphBarsDefaults);
  jpArgsParseCommon ($args);
  jpApplySettings ();

  return "Render bar graphic($input)";
}

function jpPieRender( $input, $args, $parser ) {
  global $jpgraphWikiDefaults;
  global $jpgraphPieDefaults;

  jpChartInit ();
  jpArgsParseCommon ($jpgraphWikiDefaults);
  jpArgsParseCommon ($jpgraphPieDefaults);
  jpArgsParseCommon ($args);
  jpApplySettings ();

  return "Render pie graphic($input)";
}
