<?php

namespace App\Form;

use App\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле заголовок не может быть пустым',
                    ])
                ]])
            ->add('annotation', TextType::class, [
                'label' => 'Аннотация',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле аннотация не может быть пустым',
                    ])
                ]])
            ->add('newsText', TextareaType::class, [
                'label' => 'Тело новости',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле тело новости не может быть пустым',
                    ])
                ]]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
