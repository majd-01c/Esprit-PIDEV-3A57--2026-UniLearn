<?php

namespace App\Form\IArooms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EspritScrapeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('studentId', TextType::class, [
                'required' => true,
                'label' => 'Esprit login ID / CIN',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Your portal identifier'],
                'help' => 'The robot checkbox on Esprit is handled automatically.',
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'label' => 'Esprit password',
                'attr' => ['class' => 'form-control', 'autocomplete' => 'current-password'],
            ])
            ->add('timeoutSeconds', NumberType::class, [
                'required' => true,
                'label' => 'Timeout (seconds)',
                'scale' => 0,
                'attr' => ['class' => 'form-control', 'min' => 5, 'max' => 90],
            ])
            ->add('scrape', SubmitType::class, [
                'label' => 'Scrape Emplois.aspx',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'POST',
        ]);
    }
}
