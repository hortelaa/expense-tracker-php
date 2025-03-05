<?php
$doc = <<<DOC
Descrição da aplicação.

Usage:
  cli.php add [--description A] [--amount B]
  cli.php delete [--id C]
  cli.php list
  cli.php summary
  cli.php summary [--month D]
  cli.php (--help)

Options:
  --help     Exibe a mensagem de ajuda.
  --description A         Expense Description.
  --amount B              Expense Amount.
  --id C                  Expense ID.
  --month D               Expense Month.
DOC;

require('vendor/autoload.php');

$args = Docopt::handle($doc, array('version'=>'0.1.0'));
$filePath = 'expenses.json';
$lines = count(file($filePath));

switch($args){
    case $args['add']:
        echo addExpense($args['--description'], $args['--amount'],$lines);
        break;
    case $args['delete']:
        echo deleteSummary($args['--id']);
        break;
    case $args['list']:
        echo listExpenses();
        break;
    case $args['summary'] && $args['--month']:
        echo expensesSummaryMonth($args['--month']);
        break;
    case $args['summary']:
        echo expensesSummary();
        break;

}


function addExpense($description, $amount,$lines){
    $id = 1;
    if($lines>=1){
        $expenseArrayOld = json_decode(file_get_contents('expenses.json'),true);
        foreach ($expenseArrayOld as $key => $expenses){
            $expenseArrayOld[$key]['id'] = $id;
            $id++;
        }
        $expenseArrayNew[] = ["id" => $id, "description" => $description, "amount" => $amount, "date" => date("Y-m-d")];
        $teste = array_merge($expenseArrayOld,$expenseArrayNew);
//      $expenseArrayOld[] = $expenseArrayNew;
        $jsonFinal = json_encode($teste,true);
        file_put_contents("expenses.json",$jsonFinal.PHP_EOL);
    }else{
        $expenseArray[] = ["id" => $id, "description" => $description, "amount" => $amount, "date" => date("Y-m-d")];
        $expenseArray = json_encode($expenseArray,true);
        file_put_contents("expenses.json", $expenseArray.PHP_EOL, FILE_APPEND|LOCK_EX);
    }
    return "# Expense added successfully (ID: $id) \n";
}

function listExpenses(){
    $expenseList = json_decode(file_get_contents('expenses.json'),true);
    if(empty($expenseList)){
        echo "There are no expenses registered.".PHP_EOL;
        exit;
    }
    echo "# ID  Date       Description  Amount".PHP_EOL;
    foreach($expenseList as $key => $expenses){
        echo "# ".$expenses['id']. "   ". $expenses['date']. " " . $expenses['description'].
        str_pad("$". $expenses['amount'],(16-strlen($expenses['description'])), " ", STR_PAD_LEFT). PHP_EOL;
    }
}

function expensesSummary(){
    $expenseList = json_decode(file_get_contents('expenses.json'),true);
    foreach($expenseList as $key => $expenses){
        $arraySoma[] = $expenses['amount'];
    }
    $soma = array_sum($arraySoma);
    echo "# Total expenses: $$soma".PHP_EOL;
}

function deleteSummary($id){
$expenseList = json_decode(file_get_contents('expenses.json'),true);
foreach($expenseList as $key => $expenses){
    if (in_array($id,$expenses)){
        unset($expenseList[$key]);
    }
}
$jsonFinal = json_encode($expenseList,true);
file_put_contents('expenses.json',$jsonFinal,true);
echo "# Expense deleted successfully (ID:$id)".PHP_EOL;
}

function expensesSummaryMonth($month){
    $expenseList = json_decode(file_get_contents('expenses.json'),true);
    foreach($expenseList as $key => $expenses){
        if(date("m",strtotime($expenses['date'])) == $month){
            $arraySoma[] = $expenses['amount'];
        }
    }
    if (empty($arraySoma)) {
        echo "# There are no existing expenses for " . date("F", mktime(0, 0, 0, $month)) . ".\n";
        return;
    }
    $soma = array_sum($arraySoma);
    echo "# Total expenses for " . date("F: ",mktime(0,0,0,$month)) . "$$soma".PHP_EOL;
}