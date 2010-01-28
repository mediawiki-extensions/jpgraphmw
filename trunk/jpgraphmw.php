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
require_once("$jpgraph_home/src/jpgraph_utils.inc.php");

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

$jpgraphTimeAlign = array (
// Hour adjustement
"h"    => HOURADJ_1,
"hour" => HOURADJ_1,
"1h"   => HOURADJ_1,
"2h"   => HOURADJ_2,
"3h"   => HOURADJ_3,
"4h"   => HOURADJ_4,
"6h"   => HOURADJ_6,
"12h"  => HOURADJ_12,
// min
"min"    => MINADJ_1,
"minute" => MINADJ_1,
"1min"   => MINADJ_1,
"5min"   => MINADJ_5,
"10min"  => MINADJ_10,
"15min"  => MINADJ_15,
"30min"  => MINADJ_30,
// sec
"s"      => SECADJ_1,
"sec"    => SECADJ_1,
"second" => SECADJ_1,
"1s"     => SECADJ_1,
"5s"     => SECADJ_5,
"10s"    => SECADJ_10,
"15s"    => SECADJ_15,
"30s"    => SECADJ_30,
// year
"year"   => YEARADJ_1,
"1y"     => YEARADJ_1,
"2y"     => YEARADJ_2,
"5y"     => YEARADJ_5,

"month" => MONTHADJ_1,
"1m"    => MONTHADJ_1,
"6m"    => MONTHADJ_6,

"day"   => DAYADJ_1,
"1d"    => DAYADJ_1,
"week"  => DAYADJ_7
);

$jpgraphTickAlign = array (
// Month
"month" => DSUTILS_MONTH,
"1m" => DSUTILS_MONTH,
"2m" => DSUTILS_MONTH2,
"3m" => DSUTILS_MONTH3,
"6m" => DSUTILS_MONTH6,
// Week
"week" => DSUTILS_WEEK1,
"1w" => DSUTILS_WEEK1,
"2w" => DSUTILS_WEEK2,
"4w" => DSUTILS_WEEK4,
// Day
"day" => DSUTILS_DAY1,
"1d" => DSUTILS_DAY1,
"2d" => DSUTILS_DAY2,
"3d" => DSUTILS_DAY4,
// Year
"year" => DSUTILS_YEAR1,
"1y" => DSUTILS_YEAR1,
"2y" => DSUTILS_YEAR2,
"3y" => DSUTILS_YEAR5
);

$jpgraphLabelType = array (
// Pie label type
"ABS"     => PIE_VALUE_ABS,
"PER"     => PIE_VALUE_PER,
"ADJ_PER" => PIE_VALUE_ADJPER
);

// -----------------------------------------------------------------------------

$jpgraph_help = "

<p>In order to create chart, you can use the following tags :</p>
- Simple line chart :
<pre>&lt;jpline&gt;
x,y
1,2
2,3
&lt;/jpline&gt;</pre>
- Simple bar chart :
<pre>&lt;jpbar&gt;
x,y
1,2
2,3
&lt;/jpbar&gt;</pre>
- Simple pie chart :
<pre>&lt;jppie title='an apple pie'&gt;
or,15
carbone,38
acier,47
&lt;/jppie&gt;</pre>

<p>You can use the following parameters to customize your charts :</p>
<pre>
  size               size of the graphic (by default 400x300)
  type               type of graphic. Value can be line, bar or area.
  rotatexlegend      rotate x legend of n degrees
  rotateylegend      same as above
  usettf             use ttf to render text. Use value no to disable ttf rendering
  legendposition     set legend position
  center             set pie position (ex : 0.4,0.6)
  barwidth           value of bar width
  title              title of this chart
  colors             use colors to render graphics (use ',' to use multiple colors).
  disable            use this to disable data rows (ex : disable=1,2 to disable first and second column)
  explode            use this keyword with pie chart
  margin             set margin value (ex : 10,0,10,0)
  xlabel             set xlabel
  ylabel             set ylabel
  labelformat        set pie label format
  xlabelformat       same for x label
  ylabelformat       same for y label
  fill               set fill value for background
  dateformat         set date format
  scale              scale value. Possible value are: (dat|lin|text|log|int)(lin|log|int)
  format             image format to output (default: png)
  fieldsep           change fieldsep for data parsing (default ',')
  max                set max value
  min                set min value
  timealign          align start and end of data with something. Possible values are: ".implode(", ", array_keys($jpgraphTickAlign))."
  tickalign          set tick frequency.".implode(", ", array_keys($jpgraphTickAlign))."
  horizontal         set chart direction as horizontal
  antialias          turn on/off antialias (noantialias or antialias=no)
  stacked            use stacked chart
  font               set font name. Possible values are: ".implode(", ", array_keys($jpgraphFontList))."
  linemark/mark      change linemark ".implode(", ", array_keys($jpgraphMarkList))."
  grid               draw grid. Possible values are x, y, xy or yx
</pre>
";

class JpgraphMWException extends Exception {
  public function __construct($message = null, $code = 0) {
    global $jpgraph_help;
    if(!$message) {
      parent::__construct("$message $jpgraph_help", $code);
    } else {
      parent::__construct("<pre>$message</pre>$jpgraph_help", $code);
    }
  }
  public function __toString() {
    return get_class($this)." '{$this->message}' in {$this->file}({$this->line})\n".
                            "{$this->getTraceAsString()}";
  }
}

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
  var $center;
  var $barwidth;
  var $rotatexlegend;
  var $rotateylegend;
  var $xlabel;
  var $ylabel;
  var $showlabel;
  var $labelformat;
  var $xlabelformat;
  var $ylabelformat;
  var $labeltype;
  var $haslegend;
  var $hasxgrid;
  var $hasygrid;
  var $ishorizontal;
  var $min;
  var $max;
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
  var $timealign;
  var $tickalign;
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
    $this->showlabel = true;
    $this->labelformat = false;
    $this->xlabelformat = false;
    $this->ylabelformat = false;
    $this->labeltype = "PER";
    $this->scale = false;
    $this->dateformat = false;
    $this->haslegend = false;
    $this->hasxgrid = false;
    $this->hasygrid = false;
    $this->legendposition = false;
    $this->center = "0.5,0.5";
    $this->barwidth = 0.5;
    $this->ishorizontal = false;
    $this->isantialias = true;   // use $jpgraphWikiDefaults = Array("antialias" => "no"); to disable antialias
    $this->usettf = true;        // use $jpgraphWikiDefaults = Array("usettf" => "no"); to disable ttf
    $this->rotatexlegend = 0;
    $this->rotateylegend = 0;
    $this->size = "400x300";
    $this->margin = "60,20,50,80";
    $this->title = "";
    $this->colors = "#5555ff,#55ff55@0.8,#ff55ff,#A0F000@0.8,#ffff55,#956575@0.8,".
                    "#55ffff,#ff00ff@0.8,#7f7f00,#A07fA0@0.8,#7f7f7f,#7f007f@0.8";
    $this->fill = "#EFEFEF@0.5,#BBCCFF@0.5";
    $this->isstacked = false;
    $this->is3d = false;
    $this->angle = 50;
    $this->min = 0;
    $this->max = false;
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
    $this->timealign = false;
    $this->tickalign = false;
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
    foreach($args as $name => $value) {
      if(preg_match("/help/", $name)) {
        throw new JpgraphMWException("");
      } else if(preg_match("/^(no|not)(size|type|rotatexlegend|usettf|rotateylegend|legendposition|barwidth|title|colors|nocolors|disable|".
                               "explode|center|margin|xlabel|ylabel|showlabel|labelformat|xlabelformat|ylabelformat|labeltype|fill|dateformat|scale|format|fieldsep|".
                               "max|min|timealign|tickalign)$/", $name, $field)) {
        $var = "\$this->".$field[2].' = false;';
        eval($var);
      } else if(preg_match( "/^(size|type|rotatexlegend|usettf|rotateylegend|legendposition|barwidth|title|colors|nocolors|disable|".
                               "explode|center|margin|xlabel|ylabel|showlabel|labelformat|xlabelformat|ylabelformat|labeltype|fill|dateformat|scale|format|fieldsep|".
                               "max|min|timealign|tickalign)$/", $name, $field)) {
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
          throw new JpgraphMWException("Unknown font name($value). Possible values are: ".implode(", ", array_keys($jpgraphFontList)));
        }
      } else if(preg_match("/^(linemark|mark)$/", $name)) {
        $this->mark = $value;
      } else {
        switch($name) {
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
          default:
            throw new JpgraphMWException("Unknown option '$name'.");
        }
      }
    }
  }
  function preProcess() {
    global $jpgraphTimeAlign, $jpgraphTickAlign;
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
        throw new JpgraphMWException("Error while parsing scale type. Unknown type ".$this->scale.".");
      }
    } else {
      $this->graph->SetScale("textlin");
      $this->islinear = false;
    }
    // Setting and checking time value
    if($this->timealign) {
      if(!preg_match("/,/", $this->timealign)) {
        $this->timealign .= ",".$this->timealign;
      }
      $timealign = split(",", $this->timealign);
      for($i = 0; $i < count($timealign); $i++)
        if($timealign[$i] && !array_key_exists($timealign[$i], $jpgraphTimeAlign))
          throw new JpgraphMWException("Unknown time align value (".$timealign[$i]."). Possible values are: ".
                              implode(", ", array_keys($jpgraphTimeAlign)));
    }
    if($this->tickalign && !array_key_exists($this->tickalign, $jpgraphTickAlign)) {
       throw new JpgraphMWException("Unknown tick align value (".$this->tickalign."). Possible values are: ".
                            implode(", ", array_keys($jpgraphTickAlign)));
    }
  }
  abstract function instanciateGraph();
  // part to implement in order to handle bar, line, pie etc.
  abstract function parse($input, $parser);
  // post process
  function postProcess() {
    global $jpgraphTimeAlign, $jpgraphTickAlign, $dateUtils;
    $this->color_list = split(",", $this->colors);

    if($this->hasxgrid)
      $this->graph->xgrid->Show();
    if($this->hasygrid)
      $this->graph->ygrid->Show();
    if($this->xlabel)
      $this->graph->xaxis->title->Set($this->xlabel);
    if($this->ylabel)
      $this->graph->yaxis->title->Set($this->ylabel);
    if($this->xlabelformat)
      $this->graph->xaxis->SetLabelFormatString($this->xlabelformat, $this->xistime);
    if($this->ylabelformat)
      $this->graph->yaxis->SetLabelFormatString($this->ylabelformat);
    if($this->fill) {
      $tmp = split(",", $this->fill);
      if(count($tmp) == 2) {
        $this->graph->ygrid->SetFill(true, $tmp[0], $tmp[1]);
      } else if(count($tmp) == 1) {
        $this->graph->ygrid->SetFill(true, $tmp[0], $tmp[0]);
      } else {
        throw new JpgraphMWException("Error while parsing fill value (".$this->fill.").");
      }
    }
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
    if($this->timealign) {
      $timealign = split(",", $this->timealign);
      $this->graph->xaxis->scale->SetDateAlign($jpgraphTimeAlign[$timealign[0]], $jpgraphTimeAlign[$timealign[1]]);
    }
    if($this->tickalign) {
      $dateUtils = new DateScaleUtils();
      list($tickPos,$minTickPos) = $dateUtils->getTicks($this->datax, $jpgraphTickAlign[$this->tickalign]);
      $this->graph->xaxis->SetTickPositions($tickPos, $minTickPos);
    }
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
        throw new Exception("Error while parsing '".implode($this->fieldsep, $line_array)."' : bad number of row.");
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
          $plot->SetFillColor($this->color_list[$i % count($this->color_list)]);
          break;
        case "area":
          $plot = new LinePlot($this->datay[$i], $this->datax);
          $plot->SetColor("gray");
          $plot->SetFillColor($this->color_list[$i % count($this->color_list)]);
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
          throw new JpgraphMWException("Unknown mark type(".$mark_type[$i]."). Possible values are: ".implode(", ", array_keys($jpgraphMarkList)));
        }
        $plot->mark->SetType($mark_id);
        $plot->mark->SetFillColor($this->color_list[$i % count($this->color_list)]);
      }
      $plot->SetLegend($this->labels[$i]);
      if($this->isstacked) {
        $plot_list []= $plot;
        $plot->SetColor("black");
        $plot->SetFillColor($this->color_list[$i % count($this->color_list)]);
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
    JpchartMW::JpchartMW($args, "pie");
  }
  function instanciateGraph() {
    $this->graph = new PieGraph($this->size_x, $this->size_y);
  }
  function parse($input, $parser) {
    global $jpgraphLabelType;
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
    if($this->center) {
      $tmp = split(",", $this->center);
      if(is_array($tmp) && count($tmp) == 2) {
        $pie->SetCenter($tmp[0], $tmp[1]);
      } else if(is_array($tmp) && count($tmp) == 1) {
        $pie->SetCenter($tmp[0]);
      }
    }
    if($this->labeltype) {
      $label_type = $jpgraphLabelType[$this->labeltype];
      if(!$label_type) {
        throw new JpgraphMWException("Unknown label type(".$this->labeltype."). Possible values are: ".implode(", ", array_keys($jpgraphLabelType)));
      }
      $pie->SetLabelType($label_type);
    }
    if($this->labelformat) {
      $pie->value->SetFormat($this->labelformat);
    }
    if($this->usettf)
      $pie->value->SetFont($this->font);
    $pie->value->Show($this->showlabel);
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
    return "<div style=\"border: 1px solid red; padding: 0.5em;\">".$e->getMessage()."</div>";
  }
}

function jpBarsRender($input, $args, $parser) {
  try {
    $jpchart = new JpchartMWLine($args, "bar");
    $jpchart->parse($input, $parser);
    $jpchart->postProcess();
    return $jpchart->finalize($input, $args);
  } catch(Exception $e) {
    return "<div style=\"border: 1px solid red; padding: 0.5em;\">".$e->getMessage()."</div>";
  }
}

function jpPieRender($input, $args, $parser) {
  try {
    $jpchart = new JpchartMWPie($args);
    $jpchart->parse($input, $parser);
    $jpchart->postProcess();
    return $jpchart->finalize($input, $args);
  } catch(Exception $e) {
    return "<div style=\"border: 1px solid red; padding: 0.5em;\">".$e->getMessage()."</div>";
  }
}

?>
