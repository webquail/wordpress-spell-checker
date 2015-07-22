<?php 
require('wp-load.php');
if(!is_user_logged_in()){ echo 'Необходима авторизация'; die(); }

$errorCode = [
				1 => 'Слова нет в словаре.',
				2 => 'Повтор слова.',
				3 => 'Неверное употребление прописных и строчных букв.',
				4 => 'Текст содержит слишком много ошибок. При этом приложение может отправить Яндекс.Спеллеру оставшийся непроверенным текст в следующем запросе.'
			];
$speller_url = 'http://speller.yandex.net/services/spellservice.json/checkText';
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Speller</title>
</head>
	<body>
	<div style="margin:30px 0;">
		<a href="speller.php?type=content">Проверить контент</a>&nbsp;&nbsp;&nbsp;
		<a href="speller.php?type=title">Проверить заголовки</a>
	</div>
	<?php
		if(isset($_GET['type'])){
			$posts = get_posts(['posts_per_page' => 10000]);
			foreach($posts as $post) : setup_postdata($post);
				if($_GET['type'] == 'content')
				{
					$text = preg_replace('/hellip|nbsp/ui', '', strip_tags(get_the_content()));
				}
				else
				{
					$text = preg_replace('/hellip|nbsp/ui', '', strip_tags(get_the_title()));
				}
				$data = wp_remote_request($speller_url, [
															'method'=>'POST',
															'body'=>['text'=> $text ]
														]);
			 ?>

			<?php if(isset($data['body'])){ ?>
					<?php $errors = json_decode($data['body']); if(count($errors) > 0){ ?>
						<a target="_blank"href="/wp-admin/post.php?post=<?=get_the_ID()?>&action=edit"><?=get_the_title()?></a><br>
						<?php foreach($errors as $error){ ?>
							<strong><?=$error->word;?></strong> - <?=$errorCode[$error->code]?><br/>
						<?php } ?>
						<hr />
					<?php }?>
			<?php } ?>

			<?php endforeach; wp_reset_postdata();
		}
	?>
	</body>
</html>