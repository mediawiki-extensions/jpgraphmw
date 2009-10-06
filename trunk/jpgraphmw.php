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
require_once("$jpgraph_home/src/jpgraph_pie3d.php");

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
  $wgParser->setHook('jplines', 'jpLinesRender');
  $wgParser->setHook('jpline', 'jpLinesRender');
  $wgParser->setHook('jpbars', 'jpBarsRender');
  $wgParser->setHook('jpbar', 'jpBarsRender');
  $wgParser->setHook('jppie', 'jpPieRender');
}

$jpgraphFontList = array("Ahron" => FF_AHRON,
"Arial" => FF_ARIAL,
"Big5" => FF_BIG5,
"Calculator" => FF_CALCULATOR,
"Chinese" => FF_CHINESE,
"Comic" => FF_COMIC,
"Computer" => FF_COMPUTER,
"Courier" => FF_COURIER,
"David" => FF_DAVID,
"Digital" => FF_DIGITAL,
"DV_SansSerifCond" => FF_DV_SANSSERIFCOND,
"DV_SansSerif" => FF_DV_SANSSERIF,
"DV_SansSerifMono" => FF_DV_SANSSERIFMONO,
"DV_SerifCond" => FF_DV_SERIFCOND,
"DV_Serif" => FF_DV_SERIF,
"Font0" => FF_FONT0,
"Font1" => FF_FONT1,
"Font2" => FF_FONT2,
"Georgia" => FF_GEORGIA,
"Gothic" => FF_GOTHIC,
"Mincho" => FF_MINCHO,
"Miriam" => FF_MIRIAM,
"PGothic" => FF_PGOTHIC,
"PMincho" => FF_PMINCHO,
"Simsun" => FF_SIMSUN,
"Times" => FF_TIMES,
"Trebuche" => FF_TREBUCHE,
"UserFont1" => FF_USERFONT1,
"UserFont2" => FF_USERFONT2,
"UserFont3" => FF_USERFONT3,
"UserFont" => FF_USERFONT,
"Vera" => FF_VERA,
"VeraMono" => FF_VERAMONO,
"VeraSerif" => FF_VERASERIF,
"Verdana" => FF_VERDANA);

// Main class
abstract class JpchartMW {
  var $size;
  var $sizex,$sizey;
  var $margin;
  var $title;
  var $colors;
  var $color_list;
  var $fill;
  var $isstacked;
  var $is3d;
  var $fieldsep;
  var $scale;
  var $dateformat;
  var $legendposition;
  var $rotatexlegend;
  var $rotateylegend;
  var $hasxlabel;
  var $hasylabel;
  var $haslegend;
  var $hasxgrid;
  var $hasygrid;
  var $ishorizontal;
  var $min;
  var $max;
  var $ysteps;
  var $format;
  var $isantialias;
  var $usettf;
  var $font;
  var $type;
  var $disable;
  // internal
  var $datay;
  var $datax;
  var $labels;
  var $plot_list;
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
    // internals
    $this->datay = array();
    $this->datax = false;
    $this->labels = array();
    $this->plot_list = array();
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
    global $jpgraphFontList;
    if(is_null($args)) return;
    foreach( $args as $name => $value ) {
      if(preg_match("/^(no|not)(size|type|rotatexlegend|usettf|rotateylegend|legendposition|title|colors|nocolors|disable|".
                               "margin|group|fill|nofill|dateformat|scale|format|fieldsep|max|min|ysteps)$/", $name, $field)) {
        $var = "\$this->".$field[2].' = false;';
        eval($var);
      } else if(preg_match("/^(size|type|rotatexlegend|usettf|rotateylegend|legendposition|title|colors|nocolors|disable|".
                              "margin|group|fill|nofill|dateformat|scale|format|fieldsep|max|min|ysteps)$/", $name, $field)) {
        $var = "\$this->".$field[1].' = ($value == "no" ? "" : $value);';
        eval($var);
      } else if(preg_match("/^(no)?(legend|xlabel|ylabel)$/", $name, $field)) {
        $var = '$this->has'.$field[2];
        eval("$var = (\$field[1] != 'no');");
      } else if(preg_match("/^(no|not)(horizontal|antialias|stacked|3d)$/", $name, $field)) {
        $var = '$this->is'.$field[2].' = false;';
        eval($var);
      } else if(preg_match("/^(horizontal|antialias|stacked|3d)$/", $name, $field)) {
        $var = '$this->is'.$field[1];
        eval("$var = (!preg_match(\"/^(no|not)$/\", \$value));");
      } else if(preg_match("/font/", $name)) {
        $this->font = $jpgraphFontList[$value];
        if(!$this->font) {
          throw new Exception("Unknown font name($value). Possible values are: ".implode(", ", array_values($jpgraphFont)));
        }
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
    $this->instanciateGraph();
    if($this->title) {
      $this->graph->title->Set($this->title);
      if($this->usettf)
        $this->graph->title->SetFont($this->font, FS_BOLD, 12);
    }
    if($this->isantialias)
      $this->graph->img->SetAntiAliasing();
    if($this->margin) {
      list($lm, $rm, $tm, $bm) = split(",", $this->margin);
      $this->graph->SetMargin($lm, $rm, $tm, $bm);
    }
  }
  abstract function instanciateGraph();
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
  function instanciateGraph() {
    $this->graph = new Graph($this->size_x, $this->size_y, "auto");
  }
  function parse($input, $parser) {
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

class JpchartMWPie extends JpchartMW {
  function JpchartMWPie($args) {
    JpchartMW::JpchartMW($args);
  }
  function instanciateGraph() {
    $this->graph = new PieGraph($this->size_x, $this->size_y);
  }
  function parse($input, $parser) {
    foreach(split("\n", $input) as $line) {
      // skip empty line or comments
      if(preg_match("/^(\s*)#.*$|^(\s*)$/", $line)) continue;
      // Storing data
      $raw_data = split($this->fieldsep, $line);
      if(count($raw_data) == 2) {
        $this->labels[] = $raw_data[0];
        $this->datay[] = $raw_data[1];
      } else {
        $this->datay[] = $raw_data[0];
      }
    }
    $pie = ($this->is3d ? new PiePlot3D($this->datay) : new PiePlot($this->datay));
    if(count($this->labels) == count($this->datay))
      $pie->SetLegends($this->labels);
    $this->graph->Add($pie);
  }
}

class JpchartMWBar extends JpchartMW {
  function JpchartMBar($args) {
    JpchartMW::JpchartMW($args);
  }
  function instanciateGraph() {
    $this->graph = new Graph($this->size_x, $this->size_y);
    $this->graph->SetScale("intlin");
  }
  function parseGroupBar($input) {
    $line_count = 0;
    // retrieving data
    $this->datay = array();
    foreach(split("\n", $input) as $line) {
      // skip empty line or comments
      if(preg_match("/^(\s*)#.*$|^(\s*)$/", $line)) continue;
      $line_array = split($this->fieldsep, $line);
      // Storing data
      for($i = 0; $i < count($line_array); $i++) {
        $this->datay[$i][] = $line_array[$i];
      }
      $line_count++;
    }
    if($line_count == 0) return false;
    $bar_list = array();
    // Creating data object
    for($i = 0; $i < count($this->datay); $i++) {
      $barplot = new BarPlot($this->datay[$i]);
      $barplot->SetFillColor($this->color_list[$i % count($this->color_list)]);
      /*$barplot->SetFillGradient($this->color_list[$i % count($this->color_list)],
                                  $this->color_list[($i + 1) % count($this->color_list)], GRAD_VERT);*/
      $bar_list []= $barplot;
    }
    if(count($bar_list) == 1) {
      return $bar_list[0];
    } else {
      return new AccBarPlot($bar_list);
    }
  }
  function parse($input, $parser) {
    $group_bar_data = preg_split("/groupbar:/", $input);
    $group_list = array();
    foreach($group_bar_data as $group_bar) {
      if($result = $this->parseGroupBar($group_bar))
        $group_list []= $result;
    }
    if(count($group_list) == 1) {
      $this->graph->Add($group_list[0]);
    } else {
      $this->graph->Add(new GroupBarPlot($group_list));
    }
  }
}

// -----------------------------------------------------------------------------
function jpLinesRender($input, $args, $parser) {
  try {
    $jpchart = new JpchartMWLine($args);
    $jpchart->parse($input, $parser);
    $jpchart->postProcess();
    return $jpchart->finalize($input, $args);
  } catch(Exception $e) {
    return "<pre>".$e->getMessage()."</pre>";
  }
}

function jpBarsRender($input, $args, $parser) {
  try {
    $jpchart = new JpchartMWBar($args);
    $jpchart->parse($input, $parser);
    $jpchart->postProcess();
    return $jpchart->finalize($input, $args);
  } catch(Exception $e) {
    return "<pre>".$e->getMessage()."</pre>";
  }
}

function jpPieRender($input, $args, $parser) {
  try {
    $jpchart = new JpchartMWPie($args);
    $jpchart->parse($input, $parser);
    $jpchart->postProcess();
    return $jpchart->finalize($input, $args);
  } catch(Exception $e) {
    return "<pre>".$e->getMessage()."</pre>";
  }
}

?>
