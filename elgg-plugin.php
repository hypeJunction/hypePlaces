<?php

return [
	'plugin' => [
		'name' => 'hypePlaces',
		'version' => '5.0.0',
		'description' => 'Directory of Businesses, Places of Interests and Private Locations',
		'category' => ['content', 'directory'],
		'author' => 'Ismayil Khayredinov (ismayil.khayredinov@gmail.com)',
		'website' => 'http://hypeJunction.com',
		'copyright' => '2011-2018 (c) Ismayil Khayredinov',
		'license' => 'GPL-2.0-or-later',
	],

	'bootstrap' => \hypeJunction\Places\Bootstrap::class,

	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'hjplace',
			'class' => \hypeJunction\Places\Place::class,
			'searchable' => true,
			'capabilities' => [
				'commentable' => true,
				'searchable' => true,
				'likable' => true,
			],
		],
	],

	'actions' => [
		'places/edit' => [],
		'places/delete' => [],
		'places/feature' => [
			'access' => 'admin',
		],
		'places/unfeature' => [
			'access' => 'admin',
		],
		'places/bookmark' => [],
		'places/unbookmark' => [],
		'places/checkin' => [],
		'places/checkout' => [],
	],

	'routes' => [
		'collection:object:hjplace:all' => [
			'path' => '/places',
			'resource' => 'places/all',
			'defaults' => ['filter' => 'all'],
		],
		'collection:object:hjplace:featured' => [
			'path' => '/places/featured',
			'resource' => 'places/all',
			'defaults' => ['filter' => 'featured'],
		],
		'collection:object:hjplace:owner' => [
			'path' => '/places/owner/{username}',
			'resource' => 'places/owner',
		],
		'collection:object:hjplace:group' => [
			'path' => '/places/group/{guid}',
			'resource' => 'places/group',
		],
		'collection:object:hjplace:bookmarked' => [
			'path' => '/places/bookmarked/{username}',
			'resource' => 'places/bookmarked',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'view:object:hjplace' => [
			'path' => '/places/view/{guid}/{title?}',
			'resource' => 'places/view',
		],
		'add:object:hjplace' => [
			'path' => '/places/add/{container_guid}',
			'resource' => 'places/add',
		],
		'edit:object:hjplace' => [
			'path' => '/places/edit/{guid}',
			'resource' => 'places/edit',
		],
	],

	'events' => [
		'entity:url' => [
			'object' => [
				'hypeJunction\\Places\\url_handler' => [],
			],
		],
		'entity:icon:url' => [
			'object' => [
				'hypeJunction\\Places\\entity_icon_url' => [],
			],
		],
		'entity:icon:sizes' => [
			'object' => [
				'hypeJunction\\Places\\entity_icon_sizes' => [],
			],
		],
		'search:site' => [
			'maps' => [
				'hypeJunction\\Places\\setup_site_search_maps' => [],
			],
		],
		'permissions_check' => [
			'widget_layout' => [
				'hypeJunction\\Places\\widget_layout_permissions_check' => [],
			],
		],
		'register' => [
			'menu:entity' => [
				'hypeJunction\\Places\\entity_menu_setup' => [],
				'hypeJunction\\Places\\interactions_menu_setup' => [],
			],
			'menu:owner_block' => [
				'hypeJunction\\Places\\owner_block_menu_setup' => [],
			],
			'menu:site' => [
				'hypeJunction\\Places\\site_menu_setup' => [],
			],
		],
	],

	'view_extensions' => [
		'elgg.css' => [
			'css/framework/places/css' => [],
		],
		'groups/tool_latest' => [
			'framework/places/group_module' => [],
		],
	],

	'widgets' => [
		'places' => [
			'context' => ['profile', 'dashboard'],
		],
		'places_about' => [
			'context' => ['profile', 'dashboard'],
		],
		'places_activity' => [
			'context' => ['profile', 'dashboard'],
		],
		'places_comments' => [
			'context' => ['profile', 'dashboard'],
		],
	],

	'group_tools' => [
		'places' => [
			'label' => 'places:groupoption:enable',
			'default_on' => true,
		],
	],
];
