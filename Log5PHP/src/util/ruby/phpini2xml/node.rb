class Node
  
  attr_reader :name
  attr_accessor :value
  
  # name: string
  # parent: Node
  def initialize(name, parent = nil)
    @name = name
    @children = {}
    @parent = parent
    @value = nil
  end
  
  # child_name: string
  def add_child(child_name)  
    return @children[child_name] = Node.new(child_name, self)
  end
  
  # name: string
  # return: bool
  def child?(name)
    return @children.key?(name)
  end
  
  # name: string
  def [](name)
    return @children[name]
  end
  
  # iterate across each direct child
  def each_child
    if not block_given?
      throw ArgumentError.new('Must give a block')
    end
    
    @children.each_value {|c| yield c }
  end
  
  # iterate across all descendants, depth-first search
  # must explicitly take in the block so we can pass it to the descendants
  def each_descendant(&block)
    if not block_given?
      throw ArgumentError.new('Must give a block')
    end
    
    self.each_child do |c|
      yield c
      c.each_descendant(&block)
    end
  end
  
  # return: string
  def full_key
    if @parent == nil
      return @name
    end
    
    return @parent.full_key + '.' + @name
  end
  
end