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

error_reporting(E_ALL);
$jpgraph_home = "$IP/extensions/jpgraph";

require_once("$jpgraph_home/src/jpgraph.php");
require_once("$jpgraph_home/src/jpgraph_line.php");
require_once("$jpgraph_home/src/jpgraph_bar.php");
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

// Main class
abstract class JpchartMW {
  var $fieldsep;
  var $hasxlabel;
  var $hasylabel;
  var $scale;
  var $dateformat;
  var $haslegend;
  var $hasxgrid;
  var $hasygrid;
  var $ishorizontal;
  var $size;
  var $title;
  var $colors;
  var $color_list;
  var $fill;
  var $isstacked;
  var $is3d;
  var $min;
  var $max;
  var $ysteps;
  var $format;
  var $isantialias;
  var $usettf;
  var $rotatexlegend;
  var $rotateylegend;
  var $type;
  var $margin;
  var $disable;
  var $group;
  var $font;
  // Constructor
  function JpchartMW($args) {
    global $jpgraphWikiDefaults;
    global $jpgraphLinesDefaults;

    $this->init();
    $this->parseArgs($jpgraphWikiDefaults);
    $this->parseArgs($jpgraphLinesDefaults);
    $this->parseArgs($args);
    $this->preProcess();
  }
  // default init value
  function init() {
    $this->fieldsep = ",";
    $this->hasxlabel = false;
    $this->hasylabel = false;
    $this->scale = false;
    $this->dateformat = false;
    $this->haslegend = false;
    $this->hasxgrid = false;
    $this->hasygrid = false;
    $this->ishorizontal = false;
    $this->isantialias = true;   // set to false if you don't have antialias support in GD
    $this->usettf = true;        // set to false if you don't have ttf support in GD
    $this->rotatexlegend = 0;
    $this->rotateylegend = 0;
    $this->size = "400x300";
    $this->margin = "60,20,50,80";
    $this->title = "";
    $this->colors = "#5555ff,#ff5555,#55ff55,#ff55ff,#A0F000,#ffff55,#956575,#55ffff,#ff00ff,#7f7f00,#A07fA0,#7f7f7f,#7f007f";
    $this->fill = "";
    $this->isstacked = false;
    $this->is3d = false;
    $this->min = 0;
    $this->max = false;
    $this->ysteps = 2;
    $this->format = "png";
    $this->type = "default";
    $this->disable = "";
    $this->font = FF_DV_SANSSERIF;
  }
  // debug function
  function debug($args) {
    $attr = array();    
    // make a list of attributes and their values and dump them, along with the user input
    foreach($args as $name => $value)
      $attr[] = '<strong>' . htmlspecialchars( $name ) . '</strong> = ' . htmlspecialchars( $value );
    $rslt = implode( '<br />', $attr ) . "<br />";
    return $rslt;
  }
  // Parse argument and set the parameters accordingly
  function parseArgs($args) {
    if(is_null($args)) return;
    $var = "\$this->size";
    foreach( $args as $name => $value ) {
      if(preg_match("/^(no)?(size|type|rotatexlegend|rotateylegend|title|colors|nocolors|disable|".
                    "margin|group|fill|nofill|dateformat|scale|format|fieldsep|max|min|ysteps)$/", $name, $field)) {
        $var = "\$this->".$field[2];
        eval("$var = (\$field[1] == \"no\" ? \"\" : \$value);");
      } else if(preg_match("/^(no)?(legend|xlabel|ylabel)$/", $name, $field)) {
        $var = '$this->has'.$field[2];
        eval("$var = (\$field[1] != 'no');");
      } else if(preg_match("/^(no|not)?(horizontal|antialias|stacked|3d)$/", $name, $field)) {
        $var = '$this->is'.$field[2];
        eval("$var = (!preg_match(\"/^(no|not)$/\", \$field[1]));");
      } else switch($name) {
        case "grid":
          switch($value) {
            case "xy":
              $this->hasxgrid=true;
              $this->hasygrid=true;
              break;
            case "yx":
              $this->hasxgrid=true;
              $this->hasygrid=true;
              break;
            case "x":
              $this->hasxgrid=true;
              $this->hasygrid=false;
              break;
            case "y":
              $this->hasxgrid=false;
              $this->hasygrid=true;
              break;
          }
          break;
        case "nogrid":
          $this->hasxgrid=false;
          $this->hasygrid=false;
          break;
      }
    }
  }
  function preProcess() {
    $this->color_list = split(",", $this->colors);
    for($i = 0; $i < count($this->color_list); $i++) {
      // add a '#' if the user use an hexa color
      if(preg_match("/[a-fA-F0-9]{6}/", $this->color_list[$i]))
        $this->color_list[$i] = "#".$this->color_list[$i];
    }

    list($this->size_x, $this->size_y) = split("x", $this->size);
    switch($this->type) {
      case "pie":
        $this->graph = ($this->is3d ? new PieGraph3D($this->size_x, $this->size_y) : new PieGraph($this->size_x, $this->size_y));
        break;
      default:
        $this->graph = new Graph($this->size_x, $this->size_y);
        break;
    }
    if($this->title) {
      $this->graph->title->Set($this->title);
      if($this->usettf)
        $this->graph->title->SetFont($this->font, FS_BOLD, 12);
    }
    $this->graph->img->SetAntiAliasing($this->isantialias);
    if($this->margin) {
      list($lm, $rm, $tm, $bm) = split(",", $this->margin);
      $this->graph->SetMargin($lm, $rm, $tm, $bm);
    }
  }
  // part to implement in order to handle bar, line, pie etc.
  abstract function parse($input, $parser);
  // post process
  function postProcess() {
    $this->color_list = split(",", $this->colors);

    if($this->hasxgrid)
      $this->graph->xgrid->Show();
    if($this->hasygrid)
      $this->graph->ygrid->Show();
    if($this->scale) {
      $this->graph->SetScale("datlin");
    } else {
      $this->graph->SetScale("textlin");
    }
    $this->graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#BBCCFF@0.5');
    if($this->dateformat)
      $this->graph->xaxis->scale->SetDateFormat($this->dateformat);

    if($this->usettf) {
      $this->graph->xaxis->SetFont($this->font);
      $this->graph->yaxis->SetFont($this->font);
      $this->graph->xaxis->SetLabelAngle($this->rotatexlegend);
      $this->graph->yaxis->SetLabelAngle($this->rotateylegend);
    }

    $this->graph->yaxis->scale->SetAutoMin($this->min);
    $this->graph->yaxis->scale->SetAutoMax($this->max);
  }
  // render and send back img tag
  function finalize($input, $args) {
    global $wgUploadDirectory;
    global $wgUploadPath;

    // Generating image
    $img_name = md5(implode("", $args).$input).$this->format;
    $this->graph->Stroke("$wgUploadDirectory/$img_name");
    return '<p><b><img src="'.$wgUploadPath."/".$img_name."\" alt=\"".$this->title."\"></b></p>";
  }
}

class JpchartMWLine extends JpchartMW {
  function JpchartMWLine($args) {
    JpchartMW::JpchartMW($args);
  }
  function parse($input, $parser) {
    $this->datay = array();
    $this->datax = false;
    $this->labels = array();
    $this->plot_list = array();

    // retrieving data
    $i = 0;
    $max_row_count = -1;
    foreach(split("\n", $input) as $line) {
      // skip empty line or comments
      if(preg_match("/^(\s*)#.*$|^(\s*)$/", $line)) continue;
      $line_array = split($this->fieldsep, $line);
      // if first loop => setting label and continue with next loop
      if($i == 0) {
        $this->labels = $line_array;
        $i++; continue;
      }
      // Storing data
      for($j = 0; $j < count($line_array); $j++) {
        $this->datay[$j][] = $line_array[$j];
      }
      // check data integrity
      if($max_row_count == -1)
        $max_row_count = count($line_array);
      if($max_row_count != count($line_array)) {
        throw new Exception("Error while parsing '".implode($fieldsep, $line_array)."' : bad number of row.");
      }
      $i++;
    }
    // if(x, y) curve => set datax with first set of datay
    if($this->scale) {
      $this->datax = $this->datay[0];
      $data_start = 1;
      if($this->scale == "xy") {
        $this->dateformat = "U";
      } else {
        for($i = 0; $i < count($this->datax); $i++) {
          $this->datax[$i] = strtotime($this->datax[$i]);
        }
      }
    } else {
      $data_start = 0;
    }

    $disable_row = split(",", "-1,".$this->disable);
    // Creating data object
    for($i = $data_start; $i < count($this->datay); $i++) {
      if(array_search($i, $disable_row)) continue;
      $lineplot = new LinePlot($this->datay[$i], $this->datax);
      $lineplot->mark->SetType(MARK_FILLEDCIRCLE);
      $lineplot->mark->SetFillColor($this->color_list[$i % count($this->color_list)]);
      $lineplot->SetLegend($this->labels[$i]);
      if($this->isstacked) {
        $plot_list []= $lineplot;
        $lineplot->SetColor("gray");
        $lineplot->SetFillColor($this->color_list[$i % count($this->color_list)]);
      } else {
        $lineplot->SetColor($this->color_list[$i % count($this->color_list)]);
        $this->graph->Add($lineplot);
      }
    }
    if($this->isstacked) {
      $point_area = new AccLinePlot($plot_list);
      $this->graph->Add($point_area);
    }
  }
}

// -----------------------------------------------------------------------------
function jpLinesRender( $input, $args, $parser ) {
  try {
    $jpline = new JpchartMWLine($args);
    $jpline->parse($input, $parser);
    $jpline->postProcess();
    return $jpline->finalize($input, $args);
  } catch(Exception $e) {
    return "<pre>".$e->getMessage()."</pre>";
  }
}

function jpBarsRender( $input, $args, $parser ) {
  global $jpgraphWikiDefaults;
  global $jpgraphBarsDefaults;

  /* jpChartInit();
  jpArgsParseCommon($jpgraphWikiDefaults);
  jpArgsParseCommon($jpgraphBarsDefaults);
  jpArgsParseCommon($args);
  jpApplySettings();*/

  return "Render bar graphic($input)";
}

function jpPieRender( $input, $args, $parser ) {
  global $jpgraphWikiDefaults;
  global $jpgraphPieDefaults;

  /* jpChartInit();
  jpArgsParseCommon($jpgraphWikiDefaults);
  jpArgsParseCommon($jpgraphPieDefaults);
  jpArgsParseCommon($args);
  jpApplySettings();*/

  return "Render pie graphic($input)";
}
?>
