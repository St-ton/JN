# -*- coding: utf-8 -*-
import sys, os
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

extensions = [
        'sphinx.ext.autodoc',
        'sphinx.ext.doctest',
        'sphinx.ext.todo',
        'sphinx.ext.coverage',
        'sphinx.ext.mathjax',
        'sphinx.ext.ifconfig',
        'sensio.sphinx.configurationblock'
        ]
source_suffix = '.rst'
master_doc = 'index'
project = 'JTL-Shop'
copyright = u'2010-2022, JTL-Software GmbH'
version = ''
release = ''
exclude_patterns = []
html_theme = 'sphinx_rtd_theme'
html_theme_options = {
    'navigation_depth': 3,
    'style_external_links' : True,
    'sticky_navigation' : False
}
html_theme_path = ["_themes"]
language = 'de'

htmlhelp_basename = 'Shopdoc'
man_pages = [
    (
        'index',
        'shop',
        u'JTL-Shop Documentation',
        [u'JTL-Software-GmbH'],
        1
    )
]
sys.path.append(os.path.abspath('_exts'))
lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
def setup(app):
    app.add_stylesheet('css/custom.css')  # --DEPRECATED-- in newer versions of sphinx
    #app.add_css_file('css/custom.css')
#primary_domain = 'php'
        #'sphinxcontrib.phpdomain'
