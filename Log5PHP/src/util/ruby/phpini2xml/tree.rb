require 'node'

class Tree
  
  attr_reader :root
  
  # root_name is name of the root node
  def initialize(root_name)
    @root = Node.new(root_name)
    @prefix = root_name
  end
  
  # key is a foo.bar.baz dot-delimited string
  # value is ... anything
  def add_pair(key, value)
    get_or_create_node_for_key(key).value = value
  end
  
  def node_for_key(key)
    node = traverse_nodes(key) do |current_node, key_component|
      #throw ArgumentException.new("Component #{key_component} in key #{key} doesn't exist")
      return nil
    end

    return node
  end

  private
  
  def get_or_create_node_for_key(key)
    node = traverse_nodes(key) do |current_node, key_component|
      current_node.add_child(key_component)
    end
    
    return node
  end
  
  # pass a block to be executed when a component in the key doesn't have a corresponding node
  def traverse_nodes(key)
    key_chunks = key.split('.')
        
    if key_chunks.first != @prefix
      throw ArgumentException.new("config key did not start with #{@prefix}: #{key}")
    end

    node = @root
    
    # traverse across everything after the first chunk
    key_chunks.slice(1,key_chunks.size - 1).each do |key_component|
      if not node.child?(key_component)
        yield(node, key_component)        
      end
      
      node = node[key_component]
    end
    
    return node
  end
  
end