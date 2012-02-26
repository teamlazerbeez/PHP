<?php


/**
 * @copyright Copyright © 2008, Genius.com 
 * @version $Revision: 26050 $
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Layout
 *
 * $Revision:: 26050                                      $
 * $Date:: 2008-12-18 17:10:10 -0800 (Thu, 18 Dec 2008)   $
 * $Author:: bhewitt                                      $
 */

/**
 * @ignore
 */

/**
 * Format a log event in the buzzsaw xml format
 * 
 * @package external_Log5PHP
 * @subpackage src_main_php_Log5PHP_Layout
 */
class Log5PHP_Layout_BuzzsawXML extends Log5PHP_Layout_Base
{

    /**
     * @var string hostname -- cached result
     */
    private static $hostname;

	function format(Log5PHP_LogEvent $event)
	{
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->setIndentString('  ');

		$writer->startElement('event');

		$this->addElement($writer, 'logger', $event->getLoggerName());
		$this->addElement($writer, 'message', $event->getMessage());
		$this->addElement($writer, 'level', $event->getLevel()->toString());
		$this->addElement($writer, 'thread', ''); // no threads in php
		$this->addElement($writer, 'process', $event->getThreadName()); // actually returns pid
        
		$this->addElement($writer, 'hostname', self :: getHostName());
		$this->addElement($writer, 'timestamp', $event->getTimeISO8601());

		$writer->startElement('location');
		$this->addElement($writer, 'fileName', $event->getLocationInfo()->getFileName());
		$this->addElement($writer, 'lineNumber', $event->getLocationInfo()->getLineNumber());
		$this->addElement($writer, 'method', $event->getLocationInfo()->getMethodName());
		$writer->endElement(); // location

        // todo access mdc/ndc thru the log event

		if (Log5PHP_MDC :: size() > 0)
		{
			$writer->startElement('MDC');

			foreach (Log5PHP_MDC :: keys() as $key)
			{
				$writer->startElement('mapping');

				$this->addElement($writer, 'key', $key);
				$this->addElement($writer, 'value', Log5PHP_MDC :: get($key));

				$writer->endElement(); // mapping
			}

			$writer->endElement(); // mdc
		}
        
        
        // only bother with a ndc element if there are any frames
        if (Log5PHP_NDC :: getDepth() > 0)
        {
            $ndc = Log5PHP_NDC :: get();

            $writer->startElement('NDC');

            foreach ($ndc as $frame)
            {
                $this->addElement($writer, 'frame', $frame);
            }

            $writer->endElement(); // ndc
        }

		$writer->endElement(); // event

		return $writer->outputMemory();
	}

	/**
	 * @return string xml header
	 */
	function getHeader()
	{
		return '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n" . '<eventSet version="1.0" xmlns="http://genius.com/buzzsaw">';
	}

	/**
	 * @return xml footer
	 */
	function getFooter()
	{
		return '</eventSet>';
	}

	/**
	 * Convenience method for simple elements
	 * 
	 * @param XMLWriter writer
	 * @param string elementName
	 * @param string elementText
	 */
	private function addElement(XMLWriter $writer, $elementName, $elementText)
	{
		$writer->startElement($elementName);
		$writer->text($elementText);
		$writer->endElement();
	}


    /**
     * for some reason, php doesn't have a gethostname(2) equivalent, so we have
     * to make the shell call, but to avoid making it too painful, we cache the
     * result
     * 
     * @return string hostname
     */
	private static function getHostname()
	{
        if (self :: $hostname === null)
        {
            self :: $hostname = exec('hostname');
        }
        
        return self :: $hostname;
	}
}

