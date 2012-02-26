#!/usr/bin/env ruby

# set the load path
$: << File.dirname(__FILE__)
require 'tree'
require 'util'

require 'rubygems'
gem 'libxml-ruby'
require 'xml/libxml'

=begin
  Reads in a php ini formatted log4php config file and outputs a (mostly?) equivalent xml config file
=end

lines = []
File.open(ARGV[0]) do |f|
  f.each_line do |line|
    # skip comments
    if line =~ /\s*;/
      next
    end
    
    lines << line.strip
  end
end

# get key => value association
pairs = {}
lines.each do |line|
  chunks = line.split(/ *= */, 2)
  if chunks.size != 2
    next
  end
  
  key = chunks[0]
  value = chunks[1]
  pairs[key] = value
end

tree = Tree.new('log4php')

# generate tree of keys based on '.' delimiters

pairs.each_pair do |key, value|
  tree.add_pair(key, value) 
end

root = tree.root
#puts "descendants:"
#root.each_descendant {|c| puts c.full_key + ' = ' +  c.value.to_s}

xmldoc = XML::Document.new()
xmldoc.encoding = 'UTF-8'

xmldoc.root = XML::Node.new('configuration')
xmlroot = xmldoc.root

# get the root threshold
threshold_node = tree.node_for_key('log4php.threshold')
if threshold_node
  xmldoc.root['threshold'] = threshold_node.value
end

# get the appenders
appender_root = tree.node_for_key('log4php.appender')

appender_root.each_child do |appender_node|
  app_xmlnode = XML::Node.new('appender')
  app_xmlnode['class'] = appender_node.value
  app_xmlnode['name'] = appender_node.name
  
  # configure the logger, if anny
  if layout_node = appender_node['layout']
    layout_xmlnode = XML::Node.new('layout')
    layout_xmlnode['class'] = layout_node.value
    
    layout_node.each_child do |param|
      layout_xmlnode << xml_node_for_param(param)
    end
    
    app_xmlnode << layout_xmlnode
  end
  
  appender_node.each_child do |param|
    next if param.name == 'layout'
    
    app_xmlnode << xml_node_for_param(param)
  end
  
  xmlroot << app_xmlnode
end

# root logger
rootlogger_node = tree.node_for_key('log4php.rootLogger')
if rootlogger_node
  rl_xmlnode = XML::Node.new('root')
  
  apply_xml_child_nodes_for_logger(rl_xmlnode, rootlogger_node.value)
  
  xmlroot << rl_xmlnode
end

# other loggers
logger_parent_node = tree.node_for_key('log4php.logger')
if logger_parent_node
  logger_parent_node.each_child do |logger|
    logger_xmlnode = XML::Node.new('logger')
    logger_xmlnode['name'] = logger.name
    
    apply_xml_child_nodes_for_logger(logger_xmlnode, logger.value)
    
    xmlroot << logger_xmlnode
  end
end

# TODO
# object renderers
# factories

# write out to a xml file named after the original properties file
#puts xmldoc
xmldoc.save(ARGV[0].sub('properties', 'xml'), true)