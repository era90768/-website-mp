
<?php




class orderModel{
    private $db;
    public function __construct(){
        $this->db = new database();
    }

    public function order($FullName, $Phone, $Address, $City, $Province, $Total, $Cd, $Account){
        $this->db->startTran();
        if($Account != ""){
            $this->db->Query('select id from mp_user where account=?',array($Account));
            $id_user = $this->db->fetch();
            $this->db->Query('insert into mp_order(id_user,shipping_fee,total_price,full_name,phone,address,city,province)
                values(?,"20000",?,?,?,?,?,?)', array($id_user->id,$Total,$FullName,$Phone,$Address,$City,$Province));
        } else {
            $this->db->Query('insert into mp_order(shipping_fee,total_price,full_name,phone,address,city,province)
                values("20000",?,?,?,?,?,?)', array($Total,$FullName,$Phone,$Address,$City,$Province));
        }
        
        if($this->db->rowCount() > 0){
            $this->db->Query('select id from mp_order order by id DESC');
            $id_order = $this->db->fetch();
            for($i=0; $i<Count($Cd); $i++){
                $this->db->Query('insert into mp_order_detail(id_order,id_product,quantity,price) values(?,?,?,?)',
                    array($id_order->id,$Cd[$i]['id'],$Cd[$i]['quan'],$Cd[$i]['price']));
                if($this->db->rowCount() <= 0){
                    $this->db->rollback();
                    return false;
                }
            }
            $this->db->commit();
            return true;
        } else {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
    }

    public function getAllOrder(){
        $this->db->Query("select o.id, o.full_name, o.phone, o.shipping_fee, o.total_price, o.status, 
                CONCAT(o.address,', ',o.province,', ',o.city) address, u.account, o.date_created, o.date_modify 
                FROM mp_order o left join mp_user u on o.id_user=u.id where status != 'Hoàn thành' and status!='Đã hủy'");
        return $this->db->fetchAll();
    }

    public function getAllInvoice(){
        $this->db->Query("select o.id, o.full_name, o.phone, o.shipping_fee, o.total_price, o.status, 
                CONCAT(o.address,', ',o.province,', ',o.city) address, u.account, o.date_created, o.date_modify 
                FROM mp_order o left join mp_user u on o.id_user=u.id where status = 'Hoàn thành'");
        return $this->db->fetchAll();
    }

    public function getAllOrderCancel(){
        $this->db->Query("select o.id, o.full_name, o.phone, o.shipping_fee, o.total_price, o.status, 
                CONCAT(o.address,', ',o.province,', ',o.city) address, u.account, o.date_created, o.date_modify 
                FROM mp_order o left join mp_user u on o.id_user=u.id where status='Đã hủy'");
        return $this->db->fetchAll();
    }

    public function getDetailOrder($id_o){
        $this->db->Query("select o.id, o.full_name, o.phone, o.shipping_fee, o.address,o.province,o.city
                FROM mp_order o where id=?",
                array($id_o));
        return $this->db->fetch();
    }

    public function getDetailProductOrder($id_o){
        $this->db->Query('select od.id_product, p.name, od.quantity, od.id_warehouse, od.price from mp_order_detail 
                od join mp_product p on od.id_product=p.id where od.id_order=?',array($id_o));
        return $this->db->fetchAll();
    }

    public function updateOrder($data){
        $this->db->Query('update mp_order set shipping_fee=?, total_price=?, full_name=?,
                address=?, city=?, province=? where id=?',$data);
        return $this->db->rowCount();
    }

    public function updateDetailOrder($data){
        $this->db->Query('update mp_order_detail set id_warehouse=?, quantity=?, price=?
                where id_order=? and id_product=?',$data);
        return $this->db->rowCount();
    }

    public function updateStatusConfirmed($id){
        $this->db->Query('update mp_order set status="Đã xác nhận" where id=?',array($id));
        return $this->db->rowCount();
    }

    public function updateStatusDeliver($id){
        $this->db->Query('update mp_order set status="Đang giao" where id=?',array($id));
        return $this->db->rowCount();
    }

    public function updateStatusCancel($id){
        $this->db->Query('update mp_order set status="Đã hủy" where id=?',array($id));
        return $this->db->rowCount();
    }

    public function updateStatusDone($id){
        $this->db->Query('update mp_order set status="Hoàn thành" where id=?',array($id));
        return $this->db->rowCount();
    }

    public function getStatus($id){
        $this->db->Query('select status from mp_order where id=?', array($id));
        return $this->db->fetch();
    }
}