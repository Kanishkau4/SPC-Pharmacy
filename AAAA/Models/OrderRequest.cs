namespace AAAA.Models
{
    public class OrderRequest
    {
        public int OrderId { get; set; }
        public int DrugId { get; set; }
        public int Quantity { get; set; }
        public string PharmacyEmail { get; set; }
        public decimal TotalPrice { get; set; }

        public DateTime OrderDate { get; set; }
        public string Status { get; set; }
    }
}
