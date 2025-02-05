<?php declare(strict_types=1);

namespace App\Form\Type\Forum;

use App\Command\Forum\ForumPostCommand;
use App\Form\Type\Field\SummernoteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumPostDelType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ):void
    {
        $builder
            ->add('content', SummernoteType::class, [
                'disabled'  => true,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults([
            'data_class'    => ForumPostCommand::class,
        ]);
    }
}