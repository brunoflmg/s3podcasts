<?php

	function getPagination($link, $total, $page, $limit) {
		$last = ceil($total / $limit);
		$onepage = $total > $limit ? '' : 'one-page';
		$hidden = $total > $limit ? 'hidden' : '';
		$itens = ($total == 0 || $total == 1) ? $total.' item' : $total.' itens';
		
		$html = '
			<div class="tablenav-pages">
				<span class="displaying-num">'.$itens.'</span>
				<span class="pagination-links '.$onepage.'">
		';
		
		// enable firts links
		if ($page > 1) {
			$html .= '<a href="'.$link.'&pageno='.($page-1).'" class="tablenav-pages-navspan">‹</a>';
		} else {
			$html .= '<span class="tablenav-pages-navspan">‹</span>';
		}
		
		// current page
		$html .= '<span class="paging-input">&nbsp; Página '.$page.' de '.$last.' &nbsp;</span>';
		
		// enable next links
		if ($page < $last) {
			$html .= '<a href="'.$link.'&pageno='.($page+1).'" class="tablenav-pages-navspan">›</a>';
		} else {
			$html .= '<span class="tablenav-pages-navspan">›</span>';
		}
		
		$html .= '</span></div>';
		
		return $html;
	}

	global $wpdb;
	
	$orderBy = 'id';
	$order = 'DESC';
	$limit = 10;
	$page = empty($_GET['pageno']) ? 1 : $_GET['pageno'];
	
	if (!empty($_GET['orderby'])) {
		$orderBy = $_GET['orderby'];
		$orderBy = $orderBy == 'author' ? 'user' : $orderBy;
	} 
	
	if (!empty($_GET['order'])) {
		$order = $_GET['order'];
	}
	
	// current link
	$link = "admin.php?page=s3podcasts-list&orderby=$orderBy&order=$order";
	
	// get the number of podcasts in database
	$count = $wpdb->get_row('SELECT COUNT(id) AS total FROM wp_s3podcasts');
	
	// get all podcasts    
	$results = $wpdb->get_results("SELECT * FROM wp_s3podcasts ORDER BY $orderBy $order LIMIT ".(($page - 1) * $limit).", $limit");
	
	// get pagination html
	$pagination = getPagination($link, $count->total, $page, $limit);
	
	// init order links
	$orderTitle = $orderUser = $orderDate = 'asc';
	switch ($orderBy) {
		case 'title':
			$orderTitle = $order == 'asc' ? 'desc' : 'asc';
			break;
		case 'user':
			$orderUser = $order == 'asc' ? 'desc' : 'asc';
			break;
		case 'date':
			$orderDate = $order == 'asc' ? 'desc' : 'asc';
			break; 
	}
?>
<div class="wrap">
	<h1>
		Podcasts    
		<a href="admin.php?page=s3podcasts-add" class="page-title-action">Adicionar</a>
	</h1> 
	
	<?php if (isset($_SESSION['s3podcastsmsg'])): ?>
	
	<div id="message" class="updated notice is-dismissible below-h2">
		<p><?php echo $_SESSION['s3podcastsmsg']; ?></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text">Dispensar este aviso.</span>
		</button>
	</div>
	
	<?php 
		unset($_SESSION['s3podcastsmsg']);
		endif;    
		if (empty($results)): 
	?>
	
	<div id="message" class="updated notice is-dismissible below-h2">
		<p>No momento não há podcasts cadastrados.</p>
	</div>

	<?php else: ?>
	
	<form action="admin.php?page=s3podcasts-list" id="posts-filters" method="get">
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Selecionar ação em massa</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1" selected="selected">Ações em massa</option>
					<option value="delete">Excluir permanentemente</option>
				</select>
				<input type="hidden" name="action" value="delete">
				<input type="submit" class="button action" value="Aplicar">
			</div>
			<?php echo $pagination ?>
			<br class="clear">
		</div>
		<table class="wp-list-table widefat fixed striped media">
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1">Selecionar todos</label>
						<input id="cb-select-all-1" type="checkbox">
					</td>
					<th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
						<a href="admin.php?page=s3podcasts-list&orderby=title&order=<?php echo $orderTitle; ?>">
							<span>Título</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" id="author" class="manage-column column-author sortable desc">
						<a href="admin.php?page=s3podcasts-list&orderby=author&order=<?php echo $orderUser; ?>">
							<span>Autor</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" id="date" class="manage-column column-date sortable asc">
						<a href="admin.php?page=s3podcasts-list&orderby=date&order=<?php echo $orderDate; ?>">
							<span>Data</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>    
				</tr>
			</thead>
			<tbody id="the-list">
				<?php foreach ($results as $result): ?>
				<tr id="post-7" class="author-self status-inherit">
					<th scope="row" class="check-column">            
						<label class="screen-reader-text"><?php echo $result->title ?></label>
						<input type="checkbox" id="media" name="media[]" value="<?php echo $result->id ?>">
					</th>
					<td class="title column-title has-row-actions column-primary" data-colname="Arquivo">        
						<strong class="has-media-icon">
							<a href="<?php echo $result->url ?>">                                
								
								<span aria-hidden="true"><?php echo $result->title ?></span>
								<span class="screen-reader-text">Editar</span>
							</a>                    
						</strong>
						<p class="filename">
							<?php echo $result->description ?>
						</p>
						<p>
							<audio controls>
								<source src="<?php echo $result->url ?>" type="audio/mpeg">
								Seu navegador não oferece suporte ao player de audio.
							</audio>
						</p>
						<div class="row-actions" style="margin-left: 0px;">
							<span class="delete">
								<a class="submitdelete" onclick="return showNotice.warn();" href="admin.php?page=s3podcasts-list&action=delete&id=<?php echo $result->id ?>">Excluir permanentemente</a> | 
							</span>
						</div>
					</td>
					<td class="author column-author" data-colname="Autor">
						admin
					</td>
					<td class="date column-date" data-colname="Data"><?php echo date('d/m/Y', strtotime($result->date)); ?></td>            
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="tablenav bottom">
			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Selecionar ação em massa</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1" selected="selected">Ações em massa</option>
					<option value="delete">Excluir permanentemente</option>
				</select>
				<input type="hidden" name="action" value="delete">
				<input type="submit" class="button action" value="Aplicar">
			</div>
			<?php echo $pagination ?>
			<br class="clear">
		</div>
	</form>
	<?php endif; ?>
	
</div>
