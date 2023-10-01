<?php
namespace App\Command;

use App\Entity\Country;
use App\Entity\Coupon;
use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:fill')]
class FillCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ){
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $products = [
            [
                'name' => 'Iphone',
                'price' => 100
            ],
            [
                'name' => 'Наушники',
                'price' => 20
            ],
            [
                'name' => 'Чехол',
                'price' => 10
            ],
        ];

        $countries = [
            [
                'name' => 'Germany',
                'code' => 'GE',
                'tax' => 19
            ],
            [
                'name' => 'Italy',
                'code' => 'IT',
                'tax' => 22
            ],
            [
                'name' => 'Greece',
                'code' => 'GR',
                'tax' => 24
            ],
            [
                'name' => 'France',
                'code' => 'FR',
                'tax' => 20
            ],
        ];

        $coupons = [
            [
                'discount' => 10,
                'code' => 'D15',
                'discount_type' => 'fix'
            ],
            [
                'discount' => 6,
                'code' => 'D51',
                'discount_type' => 'prc'
            ],
        ];
        foreach ($products as $product) {
            $entity = new Product();
            $entity->setName($product['name']);
            $entity->setPrice($product['price']);
            $this->em->persist($entity);
        }

        foreach ($countries as $country) {
            $entity = new Country();
            $entity->setName($country['name']);
            $entity->setCode($country['code']);
            $entity->setTax($country['tax']);
            $this->em->persist($entity);
        }

        foreach ($coupons as $coupon) {
            $entity = new Coupon();
            $entity->setDiscount(-$coupon['discount']);
            $entity->setCode($coupon['code']);
            $entity->setDiscountType($coupon['discount_type']);
            $this->em->persist($entity);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('Set test data for db')
        ;
    }
}