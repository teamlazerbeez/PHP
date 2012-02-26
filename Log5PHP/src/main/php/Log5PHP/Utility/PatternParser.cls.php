<?php
/**
 * log5php is a PHP port of the log4j java logging package.
 * 
 * <p>This framework is based on log4j (see {@link http://jakarta.apache.org/log4j log4j} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by log4j team 
 * (Ceki G�lc� as log4j project founder and 
 * {@link http://jakarta.apache.org/log4j/docs/contributors.html contributors}).</p>
 *
 * <p>PHP port, extensions and modifications by VxR. All rights reserved.<br>
 * For more information, please see {@link http://www.vxr.it/log4php/}.</p>
 *
 * <p>This software is published under the terms of the LGPL License
 * a copy of which has been included with this distribution in the LICENSE file.</p>
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Utility
 */

/**
 * @ignore 
 */
define('LOG5PHP_LOGGER_PATTERN_PARSER_ESCAPE_CHAR',         '%');

define('LOG5PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE',       0);
define('LOG5PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE',     1);
define('LOG5PHP_LOGGER_PATTERN_PARSER_MINUS_STATE',         2);
define('LOG5PHP_LOGGER_PATTERN_PARSER_DOT_STATE',           3);
define('LOG5PHP_LOGGER_PATTERN_PARSER_MIN_STATE',           4);
define('LOG5PHP_LOGGER_PATTERN_PARSER_MAX_STATE',           5);

define('LOG5PHP_LOGGER_PATTERN_PARSER_FULL_LOCATION_CONVERTER',         1000);
define('LOG5PHP_LOGGER_PATTERN_PARSER_METHOD_LOCATION_CONVERTER',       1001);
define('LOG5PHP_LOGGER_PATTERN_PARSER_CLASS_LOCATION_CONVERTER',        1002);
define('LOG5PHP_LOGGER_PATTERN_PARSER_FILE_LOCATION_CONVERTER',         1003);
define('LOG5PHP_LOGGER_PATTERN_PARSER_LINE_LOCATION_CONVERTER',         1004);

define('LOG5PHP_LOGGER_PATTERN_PARSER_RELATIVE_TIME_CONVERTER',         2000);
define('LOG5PHP_LOGGER_PATTERN_PARSER_THREAD_CONVERTER',                2001);
define('LOG5PHP_LOGGER_PATTERN_PARSER_LEVEL_CONVERTER',                 2002);
define('LOG5PHP_LOGGER_PATTERN_PARSER_NDC_CONVERTER',                   2003);
define('LOG5PHP_LOGGER_PATTERN_PARSER_MESSAGE_CONVERTER',               2004);

define('LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601',    'Y-m-d H:i:s,u'); 
define('LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ABSOLUTE',   'H:i:s');
define('LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_DATE',       'd M Y H:i:s,u');

/**
 * Most of the work of the {@link Log5PHP_PatternLayout} class 
 * is delegated to the {@link Log5PHP_PatternParser} class.
 * 
 * <p>It is this class that parses conversion patterns and creates
 * a chained list of {@link Log5PHP_PatternConverter} converters.</p>
 * 
 * @version $Revision: 26050 $ 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Utility
 *
 * @since 0.3
 */
class Log5PHP_Utility_PatternParser {

    /**
     * @var int parser state
     */
    private $state;
    
    /**
     * @var string $currentLiteral this keeps some sort of state during parsing
     */
    private $currentLiteral;
    
    private $patternLength;
    
    /**
     * @var int $i apparently used to keep track of position while parsing
     */
    private $i;
    
    /**
     * @var Log5PHP_PatternConverter
     */
    private $head = null;
     
    /**
     * @var Log5PHP_PatternConverter
     */
    private $tail = null;
    
    /**
     * @var Log5PHP_Utility_FormattingInfo
     */
    private $formattingInfo;
    
    /**
     * @var string pattern to parse
     */
    private $pattern;

    /**
     * Constructor 
     *
     * @param string $pattern
     */
    function __construct($pattern)
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternParser::LoggerPatternParser() pattern='$pattern'");
    
        $this->pattern = $pattern;
        $this->patternLength =  strlen($pattern);
        $this->formattingInfo = new Log5PHP_Utility_FormattingInfo();
        $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
    }

    /**
     * @param Log5PHP_PatternConverter $pc
     */
    function addToList($pc)
    {
        // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::addToList()");
    
        if($this->head == null) {
            $this->head = $pc;
            $this->tail = $this->head;
        } else {
            $this->tail->setNext($pc);
            $this->tail = $this->tail->getNext();
        }
    }

    /**
     * @return string
     */
    function extractOption()
    {
        if(($this->i < $this->patternLength) and ($this->pattern[$this->i] == '{')) 
        {
            $end = strpos($this->pattern, '}' , $this->i);
            if ($end !== false) 
            {
                $r = substr($this->pattern, ($this->i + 1), ($end - $this->i - 1));
                $this->i= $end + 1;
                return $r;
            }
        }
        return null;
    }

    /**
     * The option is expected to be in decimal and positive. In case of
     * error, zero is returned.  
     */
    function extractPrecisionOption()
    {
        $opt = $this->extractOption();
        $r = 0;
        if ($opt !== null) {
            if (is_numeric($opt)) {
                $r = (int)$opt;
                if($r <= 0) {
                    Log5PHP_InternalLog::warn("Precision option ({$opt}) isn't a positive integer.");
                    $r = 0;
                }
            } else {
                Log5PHP_InternalLog::warn("Category option \"{$opt}\" not a decimal integer.");
            }
        }
        return $r;
    }

    function parse()
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse()");
    
        $c = '';
        $this->i = 0;
        $this->currentLiteral = '';
        while ($this->i < $this->patternLength) {
            $c = $this->pattern{$this->i++};
//            Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() char is now '$c' and currentLiteral is '{$this->currentLiteral}'");            
            switch($this->state) {
                case LOG5PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE:
                    // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() state is 'LOG5PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE'");
                    // In literal state, the last char is always a literal.
                    if($this->i == $this->patternLength) {
                        $this->currentLiteral .= $c;
                        continue;
                    }
                    if($c == LOG5PHP_LOGGER_PATTERN_PARSER_ESCAPE_CHAR) {
                        // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() char is an escape char");                    
                        // peek at the next char.
                        switch($this->pattern{$this->i}) {
                            case LOG5PHP_LOGGER_PATTERN_PARSER_ESCAPE_CHAR:
                                // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() next char is an escape char");                    
                                $this->currentLiteral .= $c;
                                $this->i++; // move pointer
                                break;
                            case 'n':
                                // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() next char is 'n'");                            
                                $this->currentLiteral .= LOG5PHP_LINE_SEP;
                                $this->i++; // move pointer
                                break;
                            default:
                                if(strlen($this->currentLiteral) != 0) {
                                    $this->addToList(new Log5PHP_PatternConverter_Literal($this->currentLiteral));
                                    Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() Parsed LITERAL converter: \"{$this->currentLiteral}\".");
                                }
                                $this->currentLiteral = $c;
                                $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE;
                                $this->formattingInfo->reset();
                        }
                    } else {
                        $this->currentLiteral .= $c;
                    }
                    break;
              case LOG5PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE:
                    // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() state is 'LOG5PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE'");              
                    $this->currentLiteral .= $c;
                    switch($c) {
                        case '-':
                            $this->formattingInfo->setLeftAlign(true);
                            break;
                        case '.':
                            $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_DOT_STATE;
                            break;
                        default:
                            if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                                $this->formattingInfo->setMin(ord($c) - ord('0'));
                                $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_MIN_STATE;
                            } else {
                                $this->finalizeConverter($c);
                            }
                      } // switch
                    break;
              case LOG5PHP_LOGGER_PATTERN_PARSER_MIN_STATE:
                    // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() state is 'LOG5PHP_LOGGER_PATTERN_PARSER_MIN_STATE'");              
                    $this->currentLiteral .= $c;
                    if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                        $this->formattingInfo->setMin(($this->formattingInfo->getMin() * 10) + (ord(c) - ord('0')));
                    } elseif ($c == '.') {
                        $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_DOT_STATE;
                    } else {
                        $this->finalizeConverter($c);
                    }
                    break;
              case LOG5PHP_LOGGER_PATTERN_PARSER_DOT_STATE:
                    // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() state is 'LOG5PHP_LOGGER_PATTERN_PARSER_DOT_STATE'");              
                    $this->currentLiteral .= $c;
                    if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                        $this->formattingInfo->setMax(ord($c) - ord('0'));
                        $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_MAX_STATE;
                    } else {
                      Log5PHP_InternalLog::warn("LoggerPatternParser::parse() Error occured in position {$this->i}. Was expecting digit, instead got char \"{$c}\".");
                      $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
                    }
                    break;
              case LOG5PHP_LOGGER_PATTERN_PARSER_MAX_STATE:
                    // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() state is 'LOG5PHP_LOGGER_PATTERN_PARSER_MAX_STATE'");              
                    $this->currentLiteral .= $c;
                    if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                        $this->formattingInfo->setMax(($this->formattingInfo->getMax() * 10) + (ord($c) - ord('0')));
                    } else {
                      $this->finalizeConverter($c);
                      $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
                    }
                    break;
            } // switch
        } // while
        if(strlen($this->currentLiteral) != 0) {
            $this->addToList(new Log5PHP_PatternConverter_Literal($this->currentLiteral));
            // Log5PHP_InternalLog::debug("Log5PHP_PatternParser::parse() Parsed LITERAL converter: \"{$this->currentLiteral}\".");
        }
        return $this->head;
    }

    function finalizeConverter($c)
    {
        Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() with char '$c'");    

        $pc = null;
        switch($c) {
            case 'c':
                $pc = new Log5PHP_PatternConverter_Category($this->formattingInfo, $this->extractPrecisionOption());
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() CATEGORY converter.");
                // $this->formattingInfo->dump();
                $this->currentLiteral = '';
                break;
            case 'C':
                $pc = new Log5PHP_PatternConverter_ClassName($this->formattingInfo, $this->extractPrecisionOption());
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() CLASSNAME converter.");
                //$this->formattingInfo->dump();
                $this->currentLiteral = '';
                break;
            case 'd':
                $dateFormatStr = LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601; // ISO8601_DATE_FORMAT;
                $dOpt = $this->extractOption();

                if($dOpt !== null)
                    $dateFormatStr = $dOpt;
                    
                if ($dateFormatStr == 'ISO8601') {
                    $df = LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601;
                } elseif($dateFormatStr == 'ABSOLUTE') {
                    $df = LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ABSOLUTE;
                } elseif($dateFormatStr == 'DATE') {
                    $df = LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_DATE;
                } else {
                    $df = $dateFormatStr;
                    if ($df == null) {
                        $df = LOG5PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601;
                    }
                }
                $pc = new Log5PHP_PatternConverter_Date($this->formattingInfo, $df);
                $this->currentLiteral = '';
                break;
            case 'F':
                $pc = new Log5PHP_PatternConverter_Location($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_FILE_LOCATION_CONVERTER);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() File name converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'l':
                $pc = new Log5PHP_PatternConverter_Location($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_FULL_LOCATION_CONVERTER);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() Location converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'L':
                $pc = new Log5PHP_PatternConverter_Location($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_LINE_LOCATION_CONVERTER);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() LINE NUMBER converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'm':
                $pc = new Log5PHP_PatternConverter_Basic($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_MESSAGE_CONVERTER);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() MESSAGE converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'M':
                $pc = new Log5PHP_PatternConverter_Location($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_METHOD_LOCATION_CONVERTER);
                //LogLog.debug("METHOD converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'p':
                $pc = new Log5PHP_PatternConverter_Basic($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_LEVEL_CONVERTER);
                //LogLog.debug("LEVEL converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'r':
                $pc = new Log5PHP_PatternConverter_Basic($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_RELATIVE_TIME_CONVERTER);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() RELATIVE TIME converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 't':
                $pc = new Log5PHP_PatternConverter_Basic($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_THREAD_CONVERTER);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() THREAD converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'u':
                if($this->i < $this->patternLength) {
                    $cNext = $this->pattern{$this->i};
                    if(ord($cNext) >= ord('0') and ord($cNext) <= ord('9')) {
                        $pc = new Log5PHP_PatternConverter_UserField($this->formattingInfo, (string)(ord($cNext) - ord('0')));
                        Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() USER converter [{$cNext}].");
                        // formattingInfo.dump();
                        $this->currentLiteral = '';
                        $this->i++;
                    } else {
                        Log5PHP_InternalLog::warn("LoggerPatternParser::finalizeConverter() Unexpected char '{$cNext}' at position {$this->i}.");
                    }
                }
                break;
            case 'x':
                $pc = new Log5PHP_PatternConverter_Basic($this->formattingInfo, LOG5PHP_LOGGER_PATTERN_PARSER_NDC_CONVERTER);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() NDC converter.");
                $this->currentLiteral = '';
                break;

            case 'X':
                $xOpt = $this->extractOption();
                $pc = new Log5PHP_PatternConverter_MDC($this->formattingInfo, $xOpt);
                Log5PHP_InternalLog::debug("Log5PHP_PatternParser::finalizeConverter() MDC converter.");
                $this->currentLiteral = '';
                break;
            default:
                Log5PHP_InternalLog::warn("LoggerPatternParser::finalizeConverter() Unexpected char [$c] at position {$this->i} in conversion pattern.");
                $pc = new Log5PHP_PatternConverter_Literal($this->currentLiteral);
                $this->currentLiteral = '';
        }
        $this->addConverter($pc);
    }

    function addConverter($pc)
    {
        $this->currentLiteral = '';
        // Add the pattern converter to the list.
        $this->addToList($pc);
        // Next pattern is assumed to be a literal.
        $this->state = LOG5PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
        // Reset formatting info
        $this->formattingInfo->reset();
    }
}

