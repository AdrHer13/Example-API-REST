<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\WorkEntry;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkEntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
                'required' => true,
            ])
            ->add('endDate', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
                'required' => false,
            ])
            ->add('userId', EntityType::class, [
                'class' => User::class,
                'placeholder' => 'Select a valid User',
                'choice_label' => 'name',
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    $query = $er->createQueryBuilder('u')->where('u.deletedAt IS NULL');

                    return $query->orderBy('u.id', 'ASC');
                },
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkEntry::class,
        ]);
    }
}
