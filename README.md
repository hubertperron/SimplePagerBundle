Ideato\SimplePagerBundle
========================

What's inside?
--------------

This is the extension of the sfPager from sf 1.4


How to install it
-----------------

## Add SimplePagerBundle to your application kernel

	// app/AppKernel.php
	public function registerBundles() {
		$bundles = array(
			// ...
			new Ideato\SimplePagerBundle\IdeatoSimplePagerBundle(),
		);
		// ...
	}

## Register the SimplePagerBundle namespace

	// app/autoload.php
	$loader->registerNamespaces(array(
		// ...
		'Ideato'           => __DIR__.'/../vendor/bundles',
	));


## No autoloading of the service at the moment:

        //app/config.yml
        imports:
            //imports...
            - { resource: "@IdeatoSimplePagerBundle/Resources/config/services.xml" }
        

How to use it
--------------

 * Add the max_per_page parameter in your services configuration

        <parameters>
            <parameter key="ideato.pager.max_per_page">4</parameter>
        </parameters>

 * To get the pager in the controller:

        $paginator = $this->get('ideato.pager');
        $paginator->setPage($this->get('request')->query->get('page', 1));
        $paginator->setQuery($query);
        $paginator->init();

 * To display the data in the template:

        {% for blog_post in paginator %}

            <div class="post">
                ....
            </div>
        
        {% endfor %}
        
        ....
        
        {% if paginator.haveToPaginate %}
        <div class="navigation">
             {% if paginator.getNextPage() == paginator.getPage() %}
              <div class="alignleft"></div>
            {% else %}
                <div class="alignleft">
                    <a href="{{ url }}?page={{ paginator.getNextPage() }}">Next</a>
                </div>
            {% endif %}
            
            {% if 1 == paginator.getPage %}
              <div class="alignright"></div>
            {% else %}
                <div class="alignright">
                    <a href="{{ url }}?page={{ paginator.getPreviousPage() }}">Previuos</a>
                </div>
            {% endif %}
        </div>
        {% endif %}
