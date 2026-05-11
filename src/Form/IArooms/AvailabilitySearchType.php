<?php

namespace App\Form\IArooms;

use App\Entity\IArooms\TimetableUpload;
use App\Repository\IArooms\TimetableUploadRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilitySearchType extends AbstractType
{
    public function __construct(private readonly TimetableUploadRepository $timetableUploadRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('upload', EntityType::class, [
                'class' => TimetableUpload::class,
                'label' => 'Esprit scrape',
                'choice_label' => static fn (TimetableUpload $upload): string => sprintf(
                    '%s%s',
                    $upload->getOriginalFilename() ?? 'Esprit timetable',
                    $upload->getWeekStart() && $upload->getWeekEnd()
                        ? sprintf(' (%s - %s)', $upload->getWeekStart()->format('d/m/Y'), $upload->getWeekEnd()->format('d/m/Y'))
                        : ''
                ),
                'placeholder' => 'Choose an Esprit scrape',
                'required' => true,
                'query_builder' => fn ($repository) => $this->timetableUploadRepository->createQueryBuilder('u')->orderBy('u.uploadedAt', 'DESC'),
                'attr' => ['class' => 'form-select'],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endTime', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('roomFilter', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Room name'],
            ])
            ->add('buildingFilter', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Building'],
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Search availability',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
        ]);
    }
}
