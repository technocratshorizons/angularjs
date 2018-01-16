<?php $this->load->view('email/header'); ?>
<tr>
	<td>
		<p style="margin-bottom: 0; font-family: 'Raleway', sans-serif; font-size:14px; color:#555; line-height: 24px; padding:0 30px">
			<?php echo $content; ?>
		</p>
	</td>
</tr>
<?php $this->load->view('email/footer'); ?>