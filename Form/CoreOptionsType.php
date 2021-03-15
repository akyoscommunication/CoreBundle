<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoreOptionsType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('siteTitle', TextType::class, [
				'label' => 'Titre du site',
				'required' => false
			])
			->add('favicon', FileManagerType::class, [
				'label' => 'Favicon (format ico)',
				'required' => false,
			])
			->add('siteLogo', FileManagerType::class, [
				'label' => 'Logo du site',
				'required' => false,
			])
			->add('backMainColor', ColorType::class, [
				'label' => 'Couleur principale du back office',
				'required' => false
			])
			->add('hasPosts', CheckboxType::class, [
				'label' => 'Activation du blog',
				'required' => false
			])
			->add('hasPostDocuments', CheckboxType::class, [
				'label' => 'Activation des documents d\'articles',
				'required' => false
			])
			->add('orderPostsByPosition', CheckboxType::class, [
				'label' => 'Trier les articles par position ? (dans l\'admin, par défaut triés par date de création)',
				'required' => false
			])
			->add('homepage', EntityType::class, [
				'label' => "Page d'accueil",
				'required' => false,
				'class' => Page::class,
				'query_builder' => function (PageRepository $er) {
					return $er->createQueryBuilder('p')
						->orderBy('p.position', 'ASC');
				},
				'choice_label' => 'title',
				'placeholder' => "Choisissez une page"
			])
			->add('agencyName', TextType::class, [
				'label' => 'Nom de l\'agence',
				'required' => true
			])
			->add('agencyLink', UrlType::class, [
				'label' => 'Lien vers le site de l\'agence',
				'required' => true
			])
			->add('recaptchaPublicKey', TextType::class, [
				'label' => 'Clé publique reCaptcha',
				'required' => false
			])
			->add('recaptchaPrivateKey', TextType::class, [
				'label' => 'Clé privée reCaptcha',
				'required' => false
			])
			->add('emailTransport', ChoiceType::class, [
				'label' => 'Quel transport utiliser pour l\'envoi des emails (service CoreMailer) ?',
				'help' => 'L\'utilisation d\'une API nécéssite probablement de renseigner les clés API dans le fichier config/packages/core_bundle.yaml. Pour savoir comment créer ce fichier, regardez dans le Corebundle/InstallFiles/Config/core_bundle.yaml. Veuiller vérifier également que l\'expéditeur noreply@nom_de_domaine soit bien autorisé sur le compte du service.',
				'required' => false,
				'attr' => [
					'class' => 'js-select2'
				],
				'choices' => [
					'Symfony Mailer' => 'Symfony Mailer',
					'Mailjet API' => 'Mailjet API',
				],
				'data' => 'Symfony Mailer',
			])
			->add('smsTransport', ChoiceType::class, [
				'label' => 'Quel transport utiliser pour l\'envoi des SMS (service CoreSMS) ?',
				'help' => 'L\'utilisation d\'une API nécéssite probablement de renseigner les clés API dans le fichier config/packages/core_bundle.yaml. Pour savoir comment créer ce fichier, regardez dans le Corebundle/InstallFiles/Config/core_bundle.yaml.',
				'required' => false,
				'attr' => [
					'class' => 'js-select2'
				],
				'choices' => [
					'Mailjet SMS' => 'Mailjet SMS',
					'Twilio SMS' => 'Twilio SMS',
				]
			])
			->add('hasArchiveEntities', ChoiceType::class, [
				'label' => 'Activer la page archive sur les entités :',
				'choices' => $options['entities'],
				'choice_label' => function ($choice, $key, $value) {
					return $value;
				},
				'multiple' => true,
				'expanded' => true
			])
			->add('hasSingleEntities', ChoiceType::class, [
				'label' => 'Activer les pages single sur les entités :',
				'choices' => $options['entities'],
				'choice_label' => function ($choice, $key, $value) {
					return $value;
				},
				'multiple' => true,
				'expanded' => true
			])
			->add('hasSeoEntities', ChoiceType::class, [
				'label' => 'Activer le SEO sur les entités :',
				'choices' => $options['entities'],
				'choice_label' => function ($choice, $key, $value) {
					return $value;
				},
				'multiple' => true,
				'expanded' => true
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => CoreOptions::class,
			'entities' => []
		]);
	}
}
