<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Model\user\commit;
use App\Model\user\withdrawal;
use Illuminate\Http\Request;
use App\Model\user\User;
use App\Model\user\bonus;

class RecommitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:100|regex:/(^([0-9]+$)+)/',
            'userid' => 'required',
        ]);

        if($request->status == null){
            $personToPay = user::where('ref_no', $request->upline)->first();
            $bonus = $request->amount*10/100;
            $pendingTransaction = $request->amount - $bonus;

            $commit = new bonus;
            $commit->amount = $bonus;
            $commit->payid = $request->userid;
            $commit->payment = $request->amount;
            $commit->pendingTransaction = $pendingTransaction;
            $commit->receiverid = $personToPay->id;
            $commit->receivername = $personToPay->account_name;
            $commit->receiverphone = $personToPay->phoneprefix.$personToPay->phonenumber;
            $commit->receiverbank = $personToPay->bank;
            $commit->receiveraccount = $personToPay->account_no;
            $commit->save();


        return redirect(route('home'))->with('buy_message', 'You have been assigned to pay your upline a bonus of 10% of your donation which is ₦'.$bonus.'. Please make this payment immediately and contact the user for confirmation.');

        } 
        else
        {
            $findreceiver = withdrawal::where('selected', null)->where('status', null)->first();

            if(!empty($findreceiver)){
                $id = $findreceiver->id;
                $userid = $findreceiver->userid;
                $receivename = $findreceiver->name;
                $receivenumber = $findreceiver->phone;
                $receiveaccount = $findreceiver->acct_no;
                $receivebank = $findreceiver->bank;
                $money = $findreceiver->amount - $request->amount;
               
                    if($money > 0){
                        $newwithdraw = new withdrawal;
                        $newwithdraw->userid = $findreceiver->userid;
                        $newwithdraw->amount = $money;
                        $newwithdraw->name = $findreceiver->name;
                        $newwithdraw->bank = $findreceiver->bank;
                        $newwithdraw->acct_no = $findreceiver->acct_no;
                        $newwithdraw->phone = $findreceiver->phone;
                        $newwithdraw->save();
                    }

                $withdrawal = withdrawal::find($id);
                $withdrawal->selected = 1;
                $withdrawal->reamount = $request->amount;
                $withdrawal->payerid = $request->userid;
                $withdrawal->payername = $request->payername;
                $withdrawal->payerphone = $request->payerphone;
                $withdrawal->save();

            }else{
                $id = null;
                $userid = 1;
                $receivename = 'Testing One';
                $receivenumber = '+2348122334455';
                $receiveaccount = '1234567899';
                $receivebank = 'Eko Bank';
            }
            
            $commit = new commit;
            $commit->amount = $request->amount;
            $commit->pendingTransaction = $request->amount;
            $commit->invest = $request->invest;
            $commit->recommitStatus = $request->recommit;
            $commit->payid = $request->userid;
            $commit->receiverid = $userid;
            $commit->deptableid = $id;
            $commit->receivername = $receivename;
            $commit->receiverphone = $receivenumber;
            $commit->receiverbank = $receivebank;
            $commit->receiveraccount = $receiveaccount;
            $commit->save();
            
        }

        return redirect(route('home'))->with('buy_message', 'You have been assigned to pay another user. Please make this payment immediately and contact the user for confirmation.');
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:100|regex:/(^([0-9]+$)+)/',
            'payid' => 'required',
            'medium' => 'required',
            'pop' => 'required|mimes:png,jpg,jpeg,gif',
        ]);

        if($request->hasFile('pop')){
            $ImageName = $request->pop->store('public');
        }

        if($request->receiveid == null){
            //
        }else{
            if($request->receiveid == 1){
                $withdrawal = new withdrawal;
                $withdrawal->payerid = $request->payid;
                $withdrawal->payername = $request->payername;
                $withdrawal->reamount = $request->amount;
                $withdrawal->amount = $request->amount;
                $withdrawal->hepaid = 1;
                $withdrawal->userid = 1;
                $withdrawal->selected = 1;
                $withdrawal->save();

            }else{
                $withdrawal = withdrawal::find($request->tableid);
                $withdrawal->hepaid = 1;
                $withdrawal->save();
            }   
        }

        $deposit = commit::find($id);
        $deposit->payid = $request->payid;
        $deposit->amount = $request->amount;
        $deposit->notify = $request->notify;
        $deposit->medium = $request->medium;
        $deposit->bank = $request->payBank;
        $deposit->image = $ImageName;
        $deposit->save();
        
        return redirect(route('home'))->with('buy_message', 'You have succesfully uploaded payment detail. Pls call '.$request->receivename.' for confirmation');
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
