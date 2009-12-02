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

$jpgraphMarkList = array(
"Square" => MARK_SQUARE,
"Utriangle" => MARK_UTRIANGLE,
"Dtriangle" => MARK_DTRIANGLE,
"Diamond" => MARK_DIAMOND,
"Circle" => MARK_CIRCLE,
"Filledcircle" => MARK_FILLEDCIRCLE,
"Cross" => MARK_CROSS,
"Star" => MARK_STAR,
"X" => MARK_X,
"Lefttriangle" => MARK_LEFTTRIANGLE,
"Righttriangle" => MARK_RIGHTTRIANGLE,
"Flash" => MARK_FLASH,
"Img" => MARK_IMG,
"Flag1" => MARK_FLAG1,
"Flag2" => MARK_FLAG2,
"Flag3" => MARK_FLAG3,
"Flag4" => MARK_FLAG4,
"Img_pushpin" => MARK_IMG_PUSHPIN,
"Img_spushpin" => MARK_IMG_SPUSHPIN,
"Img_lpushpin" => MARK_IMG_LPUSHPIN,
"Img_diamond" => MARK_IMG_DIAMOND,
"Img_square" => MARK_IMG_SQUARE,
"Img_star" => MARK_IMG_STAR,
"Img_ball" => MARK_IMG_BALL,
"Img_sball" => MARK_IMG_SBALL,
"Img_mball" => MARK_IMG_MBALL,
"Img_lball" => MARK_IMG_LBALL,
"Img_bevel" => MARK_IMG_BEVEL);

$jpgraphFontList = array(
"Ahron" => FF_AHRON,
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
  var $explode;
  var $angle;
  var $fieldsep;
  var $scale;
  var $dateformat;
  var $legendposition;
  var $barwidth;
  var $rotatexlegend;
  var $rotateylegend;
  var $xlabel;
  var $ylabel;
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
  var $mark;
  var $type;
  var $disable;
  // internal
  var $datay;
  var $datax;
  var $labels;
  var $plot_list;
  var $islinear;
  // Constructor
  function JpchartMW($args, $default_type) {
    global $jpgraphWikiDefaults;
    global $jpgraphLinesDefaults;

    $this->init($default_type);
    $this->parseArgs($jpgraphWikiDefaults);
    $this->parseArgs($jpgraphLinesDefaults);
    $this->parseArgs($args);
    $this->preProcess();
  }

  // default init value
  function init($default_type) {
    $this->fieldsep = ",";
    $this->xlabel = false;
    $this->ylabel = false;
    $this->scale = false;
    $this->dateformat = false;
    $this->haslegend = false;
    $this->hasxgrid = false;
    $this->hasygrid = false;
    $this->legendposition = false;
    $this->barwidth = 0.5;
    $this->ishorizontal = false;
    $this->isantialias = true;   // use $jpgraphWikiDefaults = Array("antialias" => "no"); to disable antialias
    $this->usettf = true;        // use $jpgraphWikiDefaults = Array("usettf" => "no"); to disable ttf
    $this->rotatexlegend = 0;
    $this->rotateylegend = 0;
    $this->size = "400x300";
    $this->margin = "60,20,50,80";
    $this->title = "";
    $this->colors = "#5555ff,#55ff55,#ff55ff,#A0F000,#ffff55,#956575,#55ffff,#ff00ff,#7f7f00,#A07fA0,#7f7f7f,#7f007f";
    $this->fill = "";
    $this->isstacked = false;
    $this->is3d = false;
    $this->angle = 50;
    $this->min = 0;
    $this->max = false;
    $this->ysteps = 2;
    $this->format = "png";
    $this->type = $default_type;
    $this->disable = "";
    $this->font = FF_DV_SANSSERIF;
    $this->mark = "Filledcircle";
    // internals
    $this->datay = array();
    $this->datax = false;
    $this->labels = array();
    $this->plot_list = array();
    $this->xistime = false;
    $this->islinear = false;
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
      if(preg_match("/^(no|not)(size|type|rotatexlegend|usettf|rotateylegend|legendposition|barwidth|title|colors|nocolors|disable|".
                               "explode|margin|xlabel|ylabel|group|fill|dateformat|scale|format|fieldsep|max|min|ysteps)$/", $name, $field)) {
        $var = "\$this->".$field[2].' = false;';
        eval($var);
      } else if(preg_match( "/^(size|type|rotatexlegend|usettf|rotateylegend|legendposition|barwidth|title|colors|nocolors|disable|".
                               "explode|margin|xlabel|ylabel|group|fill|dateformat|scale|format|fieldsep|max|min|ysteps)$/", $name, $field)) {
        $var = "\$this->".$field[1].' = ($value == "no" ? "" : $value);';
        eval($var);
      } else if(preg_match("/^(no)?(legend|)$/", $name, $field)) {
        $var = '$this->has'.$field[2];
        eval("$var = (\$field[1] != 'no');");
      } else if(preg_match("/^(no|not)(horizontal|antialias|stacked|3d)$/", $name, $field)) {
        $var = '$this->is'.$field[2].' = false;';
        eval($var);
      } else if(preg_match("/^(horizontal|antialias|stacked|3d)$/", $name, $field)) {
        $var = '$this->is'.$field[1];
        eval("$var = (!preg_match(\"/^(no|not)$/\", \$value));");
      } else if($name == "font") {
        $this->font = $jpgraphFontList[$value];
        if(!$this->font) {
          throw new Exception("Unknown font name($value). Possible values are: ".implode(", ", array_keys($jpgraphFontList)));
        }
      } else if(preg_match("/^(linemark|mark)$/", $name)) {
        $this->mark = $value;
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
      if(preg_match("/^[a-fA-F0-9]{6}/", $this->color_list[$i]))
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
    if($this->scale) {
      if(preg_match("/^(dat|lin|text|log|int)(lin|log|int)$/", $this->scale, $tmp_scale)) {
        $this->graph->SetScale($this->scale);
        $this->islinear = preg_match("/^(lin|dat|log)$/", $tmp_scale[1]);
        $this->xistime = preg_match("/^(dat)$/", $tmp_scale[1]);
      } else {
        throw new Exception("Error while parsing scale type. Unknown type ".$this->scale.".");
      }
    } else {
      $this->graph->SetScale("textlin");
      $this->islinear = false;
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
    if($this->xlabel)
      $this->graph->xaxis->title->Set($this->xlabel);
    if($this->ylabel)
      $this->graph->yaxis->title->Set($this->ylabel);
    $this->graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#BBCCFF@0.5');
    if($this->dateformat)
      $this->graph->xaxis->scale->SetDateFormat($this->dateformat);

    if($this->usettf) {
      $this->graph->xaxis->SetFont($this->font);
      $this->graph->yaxis->SetFont($this->font);
      $this->graph->xaxis->title->SetFont($this->font);
      $this->graph->yaxis->title->SetFont($this->font);
      $this->graph->xaxis->SetLabelAngle($this->rotatexlegend);
      $this->graph->yaxis->SetLabelAngle($this->rotateylegend);
      $this->graph->legend->SetFont($this->font);
    }
    if($this->legendposition) {
      $tmp = split(",", $this->legendposition);
      $this->graph->legend->Pos($tmp[0],$tmp[1]);
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
  function JpchartMWLine($args, $type = "line") {
    JpchartMW::JpchartMW($args, $type);
  }
  function instanciateGraph() {
    $this->graph = new Graph($this->size_x, $this->size_y, "auto");
  }
  function parse($input, $parser) {
    global $jpgraphMarkList;
    $chart_type = split(",", $this->type);
    $mark_type = split(",", $this->mark);
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
    $data_start = 0;
    // if(x, y) curve => set datax with first set of datay
    if($this->islinear) {
      $this->datax = $this->datay[0];
      $data_start = 1;
      if($this->xistime) {
        for($i = 0; $i < count($this->datax); $i++) {
          $this->datax[$i] = strtotime($this->datax[$i]);
        }
      }
    }

    // Setting default value for chart type array. by default : line.
    // If only one type => applying same type for everybody
    if(count($chart_type) == 0)
      $chart_type [0]= "line";
    if(count($chart_type) != $max_row_count) {
      $tmp_type = $chart_type[0];
      $chart_type = array();
      for($i = count($chart_type); $i < $max_row_count; $i++) {
        $chart_type[$i] = $tmp_type;
      }
    }
    // same thing for mark
    if(count($mark_type) == 0)
      $mark_type [0]= "line";
    if(count($mark_type) != $max_row_count) {
      $tmp_mark = $mark_type[0];
      $mark_type = array();
      for($i = count($mark_type); $i < $max_row_count; $i++) {
        $mark_type[$i] = $tmp_mark;
      }
    }
    // Possibility to ignore data
    $disable_row = array();
    foreach(split(",", $this->disable) as $elt) {
      $disable_row[$elt] = true;
    }
    // Creating data object
    for($i = $data_start; $i < count($this->datay); $i++) {
      if(array_key_exists(($i + 1), $disable_row) && $disable_row[$i + 1]) continue;
      $show_plot = false;
      switch($chart_type[$i]) {
        case "bar":
          $plot = new BarPlot($this->datay[$i], $this->datax);
          $plot->SetWidth($this->barwidth);
          $plot->SetFillColor($this->color_list[$i % count($this->color_list)]."@0.5");
          break;
        case "area":
          $plot = new LinePlot($this->datay[$i], $this->datax);
          $plot->SetColor("gray");
          $plot->SetFillColor($this->color_list[$i % count($this->color_list)]."@0.5");
          $show_plot = true;
          break;
        default:
        case "line":
          $plot = new LinePlot($this->datay[$i], $this->datax);
          $show_plot = true;
          break;
      }
      if($show_plot) {
        $mark_id = $jpgraphMarkList[$mark_type[$i]];
        if(!$mark_id) {
          throw new Exception("Unknown mark type(".$mark_type[$i]."). Possible values are: ".implode(", ", array_keys($jpgraphMarkList)));
        }
        $plot->mark->SetType($mark_id);
        $plot->mark->SetFillColor($this->color_list[$i % count($this->color_list)]);
      }
      $plot->SetLegend($this->labels[$i]);
      if($this->isstacked) {
        $plot_list []= $plot;
        $plot->SetColor("black");
        $plot->SetFillColor($this->color_list[$i % count($this->color_list)]."@0.5");
      } else {
        $plot->SetColor($this->color_list[$i % count($this->color_list)]);
        $this->graph->Add($plot);
      }
    }
    // stacked case
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
    if($this->is3d) {
      $pie = new PiePlot3D($this->datay);
      $pie->SetAngle($this->angle);
    } else {
      $pie = new PiePlot($this->datay);
    }
    $explode_pie_list = split(",", $this->explode);
    if(count($explode_pie_list) == 1) {
      $pie->ExplodeAll($explode_pie_list[0]);
    } else {
      $pie->Explode($explode_pie_list);
    }
    if(count($this->labels) == count($this->datay))
      $pie->SetLegends($this->labels);
    $this->graph->Add($pie);
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
    $jpchart = new JpchartMWLine($args, "bar");
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
