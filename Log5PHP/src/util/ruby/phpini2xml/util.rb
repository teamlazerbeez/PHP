# general util functions

# param: general parameter (e.g. for a layout or appender)
def xml_node_for_param(param)
  param_xmlnode = XML::Node.new('param')
  param_xmlnode['name'] = param.name
  param_xmlnode['value'] = param.value
  
  return param_xmlnode
end

def apply_xml_child_nodes_for_logger(logger_node, logger_attrib_string)
  attribs = logger_attrib_string.split(/,\s*/)
  
  level = attribs[0]
  appenders = attribs.slice(1, attribs.size - 1)
  
  if level.size > 0
    level_xmlnode = XML::Node.new('level')
    level_xmlnode['value'] = level
    
    logger_node << level_xmlnode
  end
  
  appenders.each do |appender_ref|
    app_ref_xml_node = XML::Node.new('appender_ref')
    app_ref_xml_node['ref'] = appender_ref
    
    logger_node << app_ref_xml_node
  end
end