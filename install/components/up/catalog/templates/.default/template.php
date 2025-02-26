<?php

/**
 * @var array $arResult
 * @var array $arParams
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>

<main class="catalog wrapper">
	<aside class="catalog__aside">
		<form method="get">
			<?php if ($arParams['SEARCH']): ?>
				<input hidden="hidden" name="q" value="<?=$arParams['SEARCH']?>">
			<?php endif; ?>
			<h2>Фильтры</h2>
			<p class="catalog__subtitle">Категория</p>
			<ul class="filter__list">
				<?php foreach ($arResult['CATEGORIES'] as $category): ?>
					<li class="filter__item">
						<?php if (!empty($arParams['CATEGORIES_ID']) && in_array($category->getId(), $arParams['CATEGORIES_ID'], false)): ?>
						<input type="checkbox" class="filter__checkbox" name="categories[]" value="<?=$category->getId()?>" checked>
						<?php else: ?>
						<input type="checkbox" class="filter__checkbox" name="categories[]" value="<?=$category->getId()?>">
						<?php endif;?>
						<label class="filter__label"><?= htmlspecialcharsbx($category->getTitle()) ?></label>
					</li>
				<?php endforeach; ?>
			</ul>
			<button type="button" id="resetFilters"><a href="/catalog/">Очистить Мой Выбор</a></button>
			<button class="filterBtn" type="submit">Отфильтровать</button>
		</form>
	</aside>
	<section class="catalog__main">
		<?php $APPLICATION->IncludeComponent('up:task.list', '', [
			'CLIENT_ID' => (int)request()->get('user_id'),
		]);
		?>
		<?php if (!empty($arResult['SUBSCRIBER'])): ?>
			<?php if ($arResult['SUBSCRIBER']->get('SUBSCRIPTION_STATUS') !== null && $arResult['SUBSCRIBER']->get('SUBSCRIPTION_STATUS') === 'Not active'):?>
			<div class="adv">
				<a href="/subscription/" id="advLink">
					<img src="<?= SITE_TEMPLATE_PATH ?>/assets/images/ufo.svg" alt="ufo image">
				</a>
			</div>
			<?php endif;?>
		<?php endif;?>
	</section>
</main>
<script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/catalog.js"></script>