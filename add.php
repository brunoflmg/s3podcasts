<script type="text/javascript">
	function submitForm(form) {
		jQuery('#btn-submit').attr('disabled', 'disabled');
		jQuery('#btn-submit').html('<span style="line-height: 26px; height: 28px;"><img src="<?php echo get_site_url(); ?>/wp-content/plugins/s3podcasts/ajax-loader.gif"> Aguarde...</span>');
		return true;
	}
</script>
<div class="wrap">
	<h1 id="add-new-user">
		Adicionar podcast
		<a href="admin.php?page=s3podcasts-list" class="page-title-action">Voltar</a>
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
	?>

	<p>Preencha as informações abaixo e clique em Adicionar para fazer o upload do seu podcast (<b><i><u>apenas arquivo .mp3 será permitido</u></i></b>). Dependendo do tamanho do arquivo essa ação poderá demorar alguns minutos.</p>

	<form id="podcast-form" method="post" enctype="multipart/form-data" onsubmit="submitForm(this)">
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="podcast">Arquivo <span class="description">(obrigatório)</span></label></th>
					<td><input name="podcast" type="file" id="podcast" required="true" accept="audio/mp3"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="title">Título <span class="description">(obrigatório)</span></label></th>
					<td><input name="title" type="text" id="title" value="" required="true" maxlength="100" placeholder="Informe um título com no máximo 100 caracteres"></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="description"><strong>Legenda</strong></label></th>
					<td><textarea class="widefat" name="description" id="description"></textarea></td>
				</tr>
			</tbody>
		</table>
		<button type="submit" id="btn-submit" class="button button-primary">Adicionar</button> 
	</form>
</div>