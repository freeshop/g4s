<?php
include_once('./_common.php');

include_once('./_head.php');
?>

<div data-role="content" class="ui-content" role="main">
		<div class="content-primary">
		<style>
			table { width:100%; }
			table caption { text-align:left;  }
			table thead th { text-align:left; border-bottom-width:1px; border-top-width:1px; }
			table th, td { text-align:left; padding:6px;} 
		</style>
		
		
	
		
		<p>The default approach to styling content in jQuery Mobile is simple: Use a light hand.  Our goal is to let the browser's native rendering take precedence; we add a bit of padding for more comfortable readability, and use the <a href="../api/themes.html" class="ui-link">theming system</a>  to apply the font family and colors. </p>
		<p>Taking a light hand with content styling gives designers and developers a clean slate to work with, instead of fighting against a lot of complex style overhead.</p>
		
		<h2>Default HTML markup styling</h2>
		<p>By default, jQuery Mobile themes use standard HTML styles and sizes for standard markup elements like headers, paragraph content, block quotes, anchor links, standard ordered, unordered and definition lists, and tables — as shown in the examples below:</p>
		<hr>
		
		<h1>H1 Heading</h1>
		<h2>H2 Heading</h2>
		<h3>H3 Heading</h3>
		<h4>H4 Heading</h4>
		<h5>H5 Heading</h5>
		<h6>H6 Heading</h6>
		
		<p>This is a paragraph that contains <strong>strong</strong>, <em>emphasized</em> and <a href="index.html" class="ui-link">linked</a> text. Here is more text so you can see how HTML markup works in content. Here is more text so you can see how HTML markup works in content.</p>
		
		<blockquote>How about some blockquote action with a <cite>cite</cite></blockquote>
		
		<p>This is another paragraph of text so you can see how HTML markup works in content. This is another paragraph of text so you can see how HTML markup works in content. This is another paragraph of text so you can see how HTML markup works in content.</p>
		
		<p>We add a few styles to <code>tables</code> and <code>fieldsets</code> to make them more legible, which are easily overridden with custom styles.</p>
		
		<ul>
			<li>Unordered list item 1</li>
			<li>Unordered list item 1</li>
			<li>Unordered list item 1</li>
		</ul>
		
		<ol>
			<li>Ordered list item 1</li>
			<li>Ordered list item 1</li>
			<li>Ordered list item 1</li>
		</ol>
		
		<dl title="Definition list">
			<dt>Definition term</dt>
			<dd>I'm the definition text</dd>
			<dt>Definition term</dt>
			<dd>I'm the definition text</dd>
		</dl>



		<table summary="This table lists all the JetBlue flights.">
		  <caption>Travel Itinerary</caption>
		  <thead>
		    <tr>
		       <th scope="col">Flight:</th>  
		      <th scope="col">From:</th>  
		      <th scope="col">To:</th>  
		    </tr>
		  </thead>
		  <tfoot>
		    <tr>
		      <td colspan="5">Total: 3 flights</td>
		    </tr>
		  </tfoot>
		  <tbody>
		  <tr>
		    <th scope="row">JetBlue 983</th>
		    <td>Boston (BOS)</td>
		    <td>New York (JFK)</td>
		  </tr>
		  <tr>
		    <th scope="row">JetBlue 354</th>
		    <td>San Francisco (SFO)</td>
		    <td>Los Angeles (LAX)</td>
		  </tr>
		<tr>
		    <th scope="row">JetBlue 465</th>
		    <td>New York (JFK)</td>
		    <td>Portland (PDX)</td>
		  </tr>
		  </tbody>
		</table>
	
	
	</div><!--/content-primary -->		

</div>

<?
include_once('./_tail.php');
?>