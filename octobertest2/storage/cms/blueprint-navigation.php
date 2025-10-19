<?php return array (
  0 => 
  array (
    'edcd102e-0525-4e4d-b07e-633ae6c18db6' => 
    array (
      'label' => 'Blog',
      'icon' => 'octo-icon-file',
      'iconSvg' => 'modules/tailor/assets/images/blog-icon.svg',
      'order' => 95,
      '_theme' => 'demo',
      'uuid' => 'edcd102e-0525-4e4d-b07e-633ae6c18db6',
      'handle' => 'Blog\\Post',
      'hasPrimary' => true,
      'code' => 'entry_blog_post',
      'url' => 'tailor/entries/blog_post',
      'mode' => 'content',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.edcd102e05254e4db07e633ae6c18db6',
        1 => 'tailor.entry.6947ff28b66047d7924024ca6d58aeae',
        2 => 'tailor.entry.b022a74b15e64c6b9eb917efc5103543',
        3 => 'tailor.global.3328c3037989462eb86627e7037ba275',
      ),
    ),
  ),
  1 => 
  array (
    '6947ff28-b660-47d7-9240-24ca6d58aeae' => 
    array (
      'label' => 'Authors',
      'parent' => 'Blog\\Post',
      'icon' => 'octo-icon-user',
      'order' => 200,
      '_theme' => 'demo',
      'uuid' => '6947ff28-b660-47d7-9240-24ca6d58aeae',
      'handle' => 'Blog\\Author',
      'hasPrimary' => false,
      'code' => 'entry_blog_author',
      'url' => 'tailor/entries/blog_author',
      'mode' => 'content',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.6947ff28b66047d7924024ca6d58aeae',
      ),
      'parentCode' => 'entry_blog_post',
    ),
    'b022a74b-15e6-4c6b-9eb9-17efc5103543' => 
    array (
      'label' => 'Categories',
      'parent' => 'Blog\\Post',
      'icon' => 'octo-icon-list-ul',
      'order' => 150,
      '_theme' => 'demo',
      'uuid' => 'b022a74b-15e6-4c6b-9eb9-17efc5103543',
      'handle' => 'Blog\\Category',
      'hasPrimary' => false,
      'code' => 'entry_blog_category',
      'url' => 'tailor/entries/blog_category',
      'mode' => 'content',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.b022a74b15e64c6b9eb917efc5103543',
      ),
      'parentCode' => 'entry_blog_post',
    ),
    'edcd102e-0525-4e4d-b07e-633ae6c18db6' => 
    array (
      'label' => 'Posts',
      'icon' => 'octo-icon-pencil',
      'order' => 100,
      '_theme' => 'demo',
      'uuid' => 'edcd102e-0525-4e4d-b07e-633ae6c18db6',
      'handle' => 'Blog\\Post',
      'hasPrimary' => true,
      'code' => 'entry_blog_post',
      'url' => 'tailor/entries/blog_post',
      'mode' => 'content',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.edcd102e05254e4db07e633ae6c18db6',
      ),
    ),
    'a63fabaf-7c0b-4c74-b36f-7abf1a3ad1c1' => 
    array (
      'icon' => 'icon-rocket',
      'order' => 200,
      '_theme' => 'demo',
      'uuid' => 'a63fabaf-7c0b-4c74-b36f-7abf1a3ad1c1',
      'handle' => 'Page\\About',
      'hasPrimary' => false,
      'code' => 'entry_page_about',
      'url' => 'tailor/entries/page_about',
      'mode' => 'content',
      'label' => 'About Page',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.a63fabaf7c0b4c74b36f7abf1a3ad1c1',
      ),
    ),
    '339b11b7-69ad-43c4-9be1-6953e7738827' => 
    array (
      'label' => 'All Articles',
      'icon' => 'icon-wikipedia-w',
      'order' => 100,
      '_theme' => 'demo',
      'uuid' => '339b11b7-69ad-43c4-9be1-6953e7738827',
      'handle' => 'Page\\Article',
      'hasPrimary' => false,
      'code' => 'entry_page_article',
      'url' => 'tailor/entries/page_article',
      'mode' => 'content',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.339b11b769ad43c49be16953e7738827',
      ),
    ),
    '85e471d2-09b9-4f3d-a63b-1ae9d92d2879' => 
    array (
      'label' => 'Menus',
      'icon' => 'icon-sitemap',
      'order' => 300,
      '_theme' => 'demo',
      'uuid' => '85e471d2-09b9-4f3d-a63b-1ae9d92d2879',
      'handle' => 'Site\\Menus',
      'hasPrimary' => false,
      'code' => 'entry_site_menus',
      'url' => 'tailor/entries/site_menus',
      'mode' => 'content',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.85e471d209b94f3da63b1ae9d92d2879',
      ),
    ),
    '3d05b6ae-9e27-41b1-8a7b-dcc7f76a153b' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Categories',
      'icon' => 'icon-folder',
      'order' => 20,
      'mode' => 'secondary',
      '_theme' => NULL,
      'uuid' => '3d05b6ae-9e27-41b1-8a7b-dcc7f76a153b',
      'handle' => 'Wiki\\Category',
      'hasPrimary' => false,
      'code' => 'entry_wiki_category',
      'url' => 'tailor/entries/wiki_category',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.3d05b6ae9e2741b18a7bdcc7f76a153b',
      ),
    ),
    'a9dc302f-3ab9-47ee-b37a-21b4420b5a82' => 
    array (
      'primaryNavigation' => 
      array (
        'label' => 'Wiki',
        'icon' => 'icon-book',
        'order' => 150,
      ),
      'label' => 'Entries',
      'icon' => 'icon-file-text',
      'order' => 10,
      'mode' => 'primary',
      '_theme' => NULL,
      'uuid' => 'a9dc302f-3ab9-47ee-b37a-21b4420b5a82',
      'handle' => 'Wiki\\Entry',
      'hasPrimary' => false,
      'code' => 'entry_wiki_entry',
      'url' => 'tailor/entries/wiki_entry',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.a9dc302f3ab947eeb37a21b4420b5a82',
      ),
    ),
    '1c68bb14-f81e-41e9-b9e7-8b9379d8e2ef' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Revisions',
      'icon' => 'icon-history',
      'order' => 50,
      'code' => 'entry_wiki_revision',
      'mode' => 'secondary',
      '_theme' => NULL,
      'uuid' => '1c68bb14-f81e-41e9-b9e7-8b9379d8e2ef',
      'handle' => 'Wiki\\Revision',
      'hasPrimary' => false,
      'url' => 'tailor/entries/wiki_revision',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.1c68bb14f81e41e9b9e78b9379d8e2ef',
      ),
    ),
    '7c7f19d1-45b2-4cb5-84b2-c5dbecc82fd8' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Sources',
      'icon' => 'icon-link',
      'order' => 40,
      'code' => 'entry_wiki_source',
      'mode' => 'secondary',
      '_theme' => NULL,
      'uuid' => '7c7f19d1-45b2-4cb5-84b2-c5dbecc82fd8',
      'handle' => 'Wiki\\Source',
      'hasPrimary' => false,
      'url' => 'tailor/entries/wiki_source',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.7c7f19d145b24cb584b2c5dbecc82fd8',
      ),
    ),
    '14dfe87c-6db0-4f88-bd42-272c2d8740cf' => 
    array (
      'parent' => 'Wiki\\Entry',
      'label' => 'Tags',
      'icon' => 'icon-tags',
      'order' => 30,
      'mode' => 'secondary',
      '_theme' => NULL,
      'uuid' => '14dfe87c-6db0-4f88-bd42-272c2d8740cf',
      'handle' => 'Wiki\\Tag',
      'hasPrimary' => false,
      'code' => 'entry_wiki_tag',
      'url' => 'tailor/entries/wiki_tag',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.entry.14dfe87c6db04f88bd42272c2d8740cf',
      ),
    ),
    '3328c303-7989-462e-b866-27e7037ba275' => 
    array (
      'label' => 'Settings',
      'parent' => 'Blog\\Post',
      'icon' => 'octo-icon-cog',
      'order' => 200,
      '_theme' => 'demo',
      'uuid' => '3328c303-7989-462e-b866-27e7037ba275',
      'handle' => 'Blog\\Config',
      'hasPrimary' => false,
      'code' => 'global_blog_config',
      'url' => 'tailor/globals/blog_config',
      'mode' => 'settings',
      'category' => 'Globals',
      'description' => NULL,
      'permissionCode' => 
      array (
        0 => 'tailor.global.3328c3037989462eb86627e7037ba275',
      ),
      'parentCode' => 'entry_blog_post',
    ),
  ),
);