async function getSuppliers(){

    try{

        const response =
            await fetch('/api/suppliers');

        const data =
            await response.json();

        console.log(data);

        return data;

    }catch(err){

        console.error(err);

        return [];
    }
}