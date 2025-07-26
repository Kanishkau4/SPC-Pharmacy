namespace AAAA.Models
{
    public class DrugRequest
    {
        public int RequestId { get; set; }
        public string DrugName { get; set; }
        public string DrugCategory { get; set; }
        public int Quantity { get; set; }
        public string PharmacyEmail { get; set; }
        public DateTime RequestDate { get; set; } = DateTime.Now;

    }
}
